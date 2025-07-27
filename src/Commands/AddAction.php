<?php

namespace Antonella\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
 
/**
  * @see https://code.tutsplus.com/es/tutorials/how-to-create-custom-cli-commands-using-the-symfony-console-component--cms-31274
  *	https://symfony.com/doc/current/console
  *	https://symfony.com/doc/current/console/input.html
  *	https://symfony.com/doc/current/console/input.html#using-command-options		
  */

class AddAction extends BaseCommand {

	protected $namespace;

    	// the name of the command (the part after "antonella")
    	protected static $defaultName = 'add:action';
	
		protected function configure()
    	{
        
			$this->setDescription('Add a action hook')
				 ->setHelp('Example: init:ExampleController@index:10:1 [--enque | -e]')
				 ->addArgument('data', InputArgument::REQUIRED, 'The hook next to the controller and its method, Use => tag:Controller@method:priority:num_args')
				 ->addOption('enque', 'e', InputOption::VALUE_NONE, 'If set to true, the hook is added to the config.php file');
		
    	}
 
    	protected function execute(InputInterface $input, OutputInterface $output)
    	{
        // Setup custom styles for better visual output
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('comment', new OutputFormatterStyle('yellow', null, ['bold']));
        
        $output->writeln('<info>🎯 Action Hook Generator</info>');
        $output->writeln('   Creating WordPress-style action hooks...');
        $output->writeln('');

        	$data = $input->getArgument('data');
			$option = $input->getOption('enque');
			$this->addAction($data, $output, $option);
		}
	
       /**
     	 *	Añade un hook de action y lo encola al array $add_action[] del fichero config.php.
     	 *
     	 *	@param array $data argumentos de la linea de comando
     	 *	Uso
     	 *		php antonella add:action tag:Controller@method:prioridad:num_args --enque
     	 *		php antonella add:action tag:Controller@method --enque
     	 *
     	 *	@see https://developer.wordpress.org/reference/functions/add_action/
     	 */
    	public function addAction($data, $output, $option)
    	{
        try {
			// Parse input data with validation
			$output->writeln('<comment>🔍 Parsing action hook data...</comment>');
			
			// cargamos la clase Config dinámicamente
			$config = $this->newConfig();

			list($tag, $callable, $priority, $args) = array_pad(explode(':', $data), 4, null);
			
			if (!$tag || !$callable) {
				$output->writeln('<error>❌ Invalid data format. Expected: tag:Controller@method:priority:args</error>');
				$output->writeln('<info>💡 Example: init:ExampleController@index:10:1</info>');
				return 1;
			}
			
        	$priority = $priority ?? 10; 			// IF IS_NULL asigna le 10
        	$args = $args ?? 1; 					// Si IS_NULL asigna le 1
        	list($controller, $method) = array_pad(explode('@', $callable), 2, 'index');
        
			$output->writeln(sprintf('<info>⚙️  Processing: %s -> %s@%s (priority: %s, args: %s)</info>', $tag, $controller, $method, $priority, $args));
			$output->writeln('');
			
        	$namespace = $this->getNamespace();
        	$class = str_replace('/', '\\', sprintf('%s\Controllers\%s', $namespace, $controller));

        	/* Si no existe el method o el controller lo añade y/o crea */
        	if (!method_exists($class, $method)) {
				$output->writeln('<comment>🔨 Creating missing method...</comment>');
            		$this->__append_method_to_class([
                		'class' => $class,
                		'method' => $method,
                		'args' => $args, ]);
            		$output->writeln("<success>✅ Method '$method' has been added to Controller '$class'</success>");
        	} else {
				$output->writeln("<info>📝 Method '$method' already exists in Controller '$class'</info>");
			}

        	/* Encolamos el metodo al array $actions[] de config.php */
        	if ($option) {
				$output->writeln('');
				$output->writeln('<comment>📦 Updating configuration file...</comment>');
				
				try {
					$target = $this->getPath('config');						// src/config.php
					
					if (!file_exists($target)) {
						$output->writeln('<error>❌ Configuration file not found: ' . $target . '</error>');
						return 1;
					}
					
            			$content = explode("\n", file_get_contents($target));

            			// $class = ltrim($class, $namespace); 					// removemos el namespace
            			$class = substr( $class, strlen($namespace));
					
					//$key = [$tag, [$this->getNamespace().$class,$method]];
					$key = [$tag, $this->getNamespace().$class, $method];
					if ( $this->__in_array($key, $config->add_action) ) {
						$output->writeln("<info>⚠️  Action hook already exists in \$add_action[] - skipping duplicate</info>");
						return 0;
					}

					$this->__search_and_replace($content,
            			[
                			'public$add_action=[];' => sprintf("\tpublic \$add_action = [\n\t\t['%s', [__NAMESPACE__ . '%s','%s'], %s, %s]\n\t];", $tag, $class, $method, $priority, $args),
                			'public$add_action=[' => sprintf("\tpublic \$add_action = [\n\t\t['%s', [__NAMESPACE__ . '%s','%s'], %s, %s]", $tag, $class, $method, $priority, $args),	// append
            			]);

            			$newContent = implode("\n", $content);
            			file_put_contents($target, $newContent);
            			$output->writeln("<success>✅ Action hook added to \$add_action[] array</success>");
            			$output->writeln("<success>✅ Config.php file has been updated successfully</success>");
					
				} catch (\Exception $e) {
					$output->writeln('<error>❌ Error updating configuration: ' . $e->getMessage() . '</error>');
					return 1;
				}
        	} else {
				$output->writeln('');
				$output->writeln('<info>💡 Tip: Use --enque (-e) flag to add this hook to config.php</info>');
			}
			
			$output->writeln('');
			$output->writeln('<success>✨ Action hook generation completed successfully!</success>');
			return 0;
			
		} catch (\Exception $e) {
			$output->writeln('<error>❌ Unexpected error: ' . $e->getMessage() . '</error>');
			return 1;
		}
    	}
}