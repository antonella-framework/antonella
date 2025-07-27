<?php

namespace Antonella\Commands;
 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
 
/**
  * @see https://code.tutsplus.com/es/tutorials/how-to-create-custom-cli-commands-using-the-symfony-console-component--cms-31274
  *		 https://symfony.com/doc/current/console
  *		 https://symfony.com/doc/current/console/input.html
  *		 https://symfony.com/doc/current/console/input.html#using-command-options		
  */

class MakeShortcode extends BaseCommand {

    // the name of the command (the part after "antonella")
    protected static $defaultName = 'make:shortcode';
    
    protected $namespace;

    protected function configure()
    {
        
		$this->setDescription('Make a shortcode')
			 ->setHelp('Example: name:ExampleController@shortcode [--enque | -e]')
             ->addArgument('data', InputArgument::REQUIRED, 'The shortcode next to the controller and its method, Use => name:Controller@method')
			 ->addOption('enque', 'e', InputOption::VALUE_NONE, 'If set to true, the new shortcode is added to the config.php file');
		
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup custom styles for better visual output
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('comment', new OutputFormatterStyle('yellow', null, ['bold']));
        
        $output->writeln('<info>📜 Shortcode Generator</info>');
        $output->writeln('   Creating new WordPress shortcode...');
        $output->writeln('');

        $data = $input->getArgument('data');
        if (empty($data)) {
            $output->writeln('<error>❌ Shortcode data cannot be empty</error>');
            $output->writeln('<info>💡 Example: php antonella make:shortcode myshortcode:Controller@method</info>');
            return 1;
        }
        
		$option = $input->getOption('enque');
        $output->writeln(sprintf('<comment>🔨 Generating shortcode: %s%s</comment>', $data, $option ? ' (with auto-enqueue)' : ''));
        
        try {
            $this->makeShortcode($data, $output, $option);
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error creating shortcode: ' . $e->getMessage() . '</error>');
            return 1;
        }
	}

	
	/**
     * Crea un shortCode y lo añade al array $shortcodes[] del fichero config.php.
     *
     * @param array $data Datos de entrada
     * 	Use:
     *  	php antonella make:shortcode name:Controller@method [--enque | -e]
     */
    protected function makeShortcode($data, $output, $option)
    {
        
        // cargamos la clase Config dinámicamente
		$config = $this->newConfig();
		
		list($tag, $callable) = explode(':', $data);
        list($controller, $method) = array_pad(explode('@', $callable), 2, 'short_code');
        
        $namespace = $this->getNamespace();
        $class = str_replace('/', '\\', sprintf('%s\Controllers\%s', $namespace, $controller));


        /* Si no existe el method o el controller lo añade y/o crea */
        if (!method_exists($class, $method)) {
            $this->__append_method_to_class([
                'class' => $class,
                'method' => $method,
            ]);
            $output->writeln("<info>The method $method has been added to Controller $class.php</info>");
        }

        /* Encolamos el metodo al array $actions[] de config.php */
        if ($option) {
            $target = $this->getPath('config');							// src/config.php
            $content = explode("\n", file_get_contents($target));

                                                                        // $class = ltrim($class, $namespace); // removemos el namespace
            $class = substr( $class, strlen($namespace));
            
            //$key = [$tag, sprintf("%s%s::%s",$this->getNamespace(),$class,$method)];
            $key = [$tag, $this->getNamespace().$class, $method];
            if ( $this->__in_array($key, $config->shortcodes) ) {
                $output->writeln("<info>the record was not added, because it already exists in \$shortcodes[]</info>");
                die();
            }
            
            $this->__search_and_replace($content,
            [
                'public$shortcodes=[];' => sprintf("\tpublic \$shortcodes = [\n\t\t['%s', __NAMESPACE__ . '%s::%s']\n\t];", $tag, $class, $method),
                'public$shortcodes=[' => sprintf("\tpublic \$shortcodes = [\n\t\t['%s', __NAMESPACE__ . '%s::%s']", $tag, $class, $method),	// append
            ]);

            $newContent = implode("\n", $content);
            file_put_contents($target, $newContent);
            $output->writeln("<info>The array shortcodes has been updated</info>");
            $output->writeln("<info>The Config.php File has been updated</info>");
        }
	}
}