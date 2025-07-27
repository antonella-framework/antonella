<?php

namespace Antonella\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
 
/**
  * @see https://code.tutsplus.com/es/tutorials/how-to-create-custom-cli-commands-using-the-symfony-console-component--cms-31274
  *		 https://symfony.com/doc/current/console
  *		 https://symfony.com/doc/current/console/input.html
  *		 https://symfony.com/doc/current/console/input.html#using-command-options
  */
class MakeHelper extends BaseCommand
{
    
    // the name of the command (the part after "antonella")
    protected static $defaultName = 'make:helper';

    protected $namespace;

    protected function configure()
    {
        $this->setDescription('Make a helper file in the folder src/Helpers')
             ->setHelp('Set a name for you helper. For example auxiliarHelper')
             ->addArgument('name', InputArgument::REQUIRED, 'Name to helper file');
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup custom styles for better visual output
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('comment', new OutputFormatterStyle('yellow', null, ['bold']));
        
        $output->writeln('<info>🔧 Helper Generator</info>');
        $output->writeln('   Creating new helper class...');
        $output->writeln('');

        $inputName = $input->getArgument('name');
        if (empty($inputName)) {
            $output->writeln('<error>❌ Helper name cannot be empty</error>');
            $output->writeln('<info>💡 Example: php antonella make:helper auxiliarHelper</info>');
            return 1;
        }
        
        $name = rtrim($inputName, '.php');   // removemos el .php
        $output->writeln(sprintf('<comment>🔨 Generating helper: %s.php</comment>', $name));
        
        try {
            $this->makeHelper($name);
            $output->writeln('');
            $output->writeln('<success>✅ Helper created successfully!</success>');
            $output->writeln(sprintf('<info>📁 Location: src/Helpers/%s.php</info>', $name));
            $output->writeln('<info>💡 You can now add utility functions to your helper</info>');
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error creating helper: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }
    /**
     * Crea un fichero helpers para albergar funciones auxiliares.
     *
     * @param array $data argumentos de la linea de comandos
     *                    donde $data[2] representa el nombre del fichero
     *                    Uso:	php antonella make:helper auxiliares
     *                    Out: src/Helpers/auxiliares.php
     */
    public function makeHelper($data)
    {
        $this->namespace = $this->getNamespace();
        $target = $this->getPath('helpers', $data);
        
        // Si la ruta no existe la crea
        if (!file_exists(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }
        
        $StubGenerator = 'Antonella\Classes\StubGenerator';
        $stub = new $StubGenerator(
            'vendor/antonella-framework/antonella/src/Stubs/helper.stub',
            //$this->getPath('stubs', 'helper'),			// 'dev/stubs/helper.stub',
            $target
        );

        $folder = array_reverse(explode('/', dirname($target)))[0];
        $stub->render([
            '%NAME%' => $data,
        ]);
    }
}
