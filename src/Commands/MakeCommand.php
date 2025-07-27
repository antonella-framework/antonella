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
class MakeCommand extends BaseCommand {
	
    // the name of the command (the part after "antonella")
    protected static $defaultName = 'make:command';

    protected $namespace;

    protected function configure()
    {
        $this->setDescription('Make a new Command')
             ->setHelp('Set a name for you new command.')
             ->addArgument('name', InputArgument::REQUIRED, 'Name new command')
			 ->addArgument('short-code', InputArgument::REQUIRED, 'Short code command, use shortcode or short:code');
       
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup custom styles for better visual output
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('comment', new OutputFormatterStyle('yellow', null, ['bold']));
        
        $output->writeln('<info>⚙️ Command Generator</info>');
        $output->writeln('   Creating new console command...');
        $output->writeln('');
        
        $name = $input->getArgument('name');
        $shortCode = $input->getArgument('short-code');
        
        if (empty($name) || empty($shortCode)) {
            $output->writeln('<error>❌ Command name and short-code cannot be empty</error>');
            $output->writeln('<info>💡 Example: php antonella make:command MyCommand my:command</info>');
            return 1;
        }
        
        $data = [
			'name' => rtrim($name, '.php'),   // removemos el .php si esta incluido
			'short_code' => $shortCode
		];
		
        $output->writeln(sprintf('<comment>🔨 Generating command: %s (%s)</comment>', $data['name'], $data['short_code']));
        
        try {
            $this->makeNewCommand($data, $output);
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error creating command: ' . $e->getMessage() . '</error>');
            return 1;
        }
	}
    /**
     * Crea un fichero helpers para albergar funciones auxiliares.
     *
     * @param array $data Nombre del comando junto a su short code
     * 	Uso:	php antonella make:command <name> short:code
     */
    public function makeNewCommand($data, $output)
    {
		extract($data);
		
        $this->namespace = $this->getNamespace();
		$name = strrpos($name, 'Command') === false ? $name.'Command' : $name;
		$target = $this->getPath('commands', $name);
		
		// Crea una clase a partir de una fichero plantilla (dev/stubs/command.stub)
        $StubGenerator = 'Antonella\Classes\StubGenerator';
        $stub = new $StubGenerator(
            'vendor/antonella-framework/antonella/src/Stubs/command.stub',
            //$this->getPath('stubs', 'command'),				// 'dev/stubs/command.stub',
            $target
        );
		
		// remplace
		$stub->render([
			'%NAMESPACE%' => 'Antonella\\Commands',
			'%CLASSNAME%' => $name,
			'%SHORTCODE%' => $short_code
		]);
		
		$output->writeln("<info>The ClassName $name.php created into dev/Commands folder</info>");
    }
    
}