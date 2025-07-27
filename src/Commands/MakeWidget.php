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

class MakeWidget extends BaseCommand {

    // the name of the command (the part after "antonella")
    protected static $defaultName = 'make:widget';

    protected $namespace;

    protected function configure()
    {
        
		$this->setDescription('Make a widget in the folder src/Widgets')
			 ->setHelp('Set a name for you widget. For example MyFirstWidget [--enque | -e]')
             ->addArgument('name', InputArgument::REQUIRED, 'Name for you widget')
			 ->addOption('enque', 'e', InputOption::VALUE_NONE, 'If set to true, the new widget is added to the config.php file');
		
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup custom styles for better visual output
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('comment', new OutputFormatterStyle('yellow', null, ['bold']));
        
        $output->writeln('<info>🧩 Widget Generator</info>');
        $output->writeln('   Creating new WordPress widget...');
        $output->writeln('');

        $inputName = $input->getArgument('name');
        if (empty($inputName)) {
            $output->writeln('<error>❌ Widget name cannot be empty</error>');
            $output->writeln('<info>💡 Example: php antonella make:widget MyWidget</info>');
            return 1;
        }
        
        $name = $this->prepare(ucfirst($inputName));
        $option = $input->getOption('enque');
        
        $output->writeln(sprintf('<comment>🔨 Generating widget: %s%s</comment>', $name, $option ? ' (with auto-enqueue)' : ''));
        
        try {
            $this->makeWidget($name, $output, $option);
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error creating widget: ' . $e->getMessage() . '</error>');
            return 1;
        }
	}

	
	/**
     *	Crea un widget desde la consola.
     *
     *	@param array $data arguments leidos desde la consola
	 *	@param OutputInterface $output Salida por pantalla
	 *  @param string $option option
     *	@param string --enque Optional indica si queremos añadirlo al array widgets de config.php
     *	example => php antonella widget MyFirstWidget
     * 	example => php antonella widget MyFirtWidget [--enque | -e]
     */
    protected function makeWidget($data, $output, $option)
    {
        
		$namespace = $this->getNamespace();

        $target = $this->getPath('widgets', $data);
        if (!file_exists(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }
		
        // Crea una clase a partir de una fichero plantilla (dev/stubs/controller.stub)
        $StubGenerator = 'Antonella\Classes\StubGenerator';
        $stub = new $StubGenerator(
            'vendor/antonella-framework/antonella/src/Stubs/widget.stub',
          //  $this->getPath('stubs', 'widget'),	//__DIR__ . '/dev/stubs/widget.stub',
            $target
        );

        $stub->render([
            '%NAMESPACE%' => $namespace.'\\Widgets',
            '%CLASSNAME%' => $data,
        ]);

        /* Comprobamos si hemos pasado el parametro --enque */
        if ($option) {
            $target = $this->getPath('config');							// src/config.php
            $content = explode("\n", file_get_contents($target));

            $this->__search_and_replace($content,
            [
                'public$widgets=[];' => sprintf("\tpublic \$widgets = [ \n\t\t__NAMESPACE__ . '\Widgets\%s'\n\t];", $data),
                'public$widgets=[' => sprintf("\tpublic \$widgets = [ \n\t\t__NAMESPACE__ . '\Widgets\%s',", $data),	// append
            ]);

            $newContent = implode("\n", $content);
            file_put_contents($target, $newContent);

            $output->writeln("<info>The Config.php File has been updated</info>");
		}

        $output->writeln("<info>The Widget $data.php created into src/Widgets folder</info>");
	}

    private function prepare($name) {
        $name = rtrim($name, '.php');                               // removemos el .php
        $name = rtrim($name, 'Widget').'Widget';                    // removemos el sufix 'Widget' y lo añadimos

        return $name;
    }
}