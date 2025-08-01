<?php
    
namespace Antonella\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 *	@see https://symfony.com/doc/current/console.html
 *
 *	run
 *		php antonella serve
 *		php antonella --force
 *		php antonella --port 8080 --force
 */
class ServeCommand extends BaseCommand {
	
	 // the name of the command (the part after "antonella")
    protected static $defaultName = 'serve';
	
	protected function configure()
    {
        $this->setDescription('Start Docker development environment')
            ->setHelp('php antonella serve - Starts Docker from two levels up');
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup custom styles for better visual output
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('comment', new OutputFormatterStyle('yellow', null, ['bold']));
        
        $output->writeln('<info>🐳 Docker Development Environment</info>');
        $output->writeln('   Starting Docker containers from project root...');
        $output->writeln('');
        
        try {
            // Get current directory (framework directory)
            $currentDir = $this->getDirBase();
            $dockerDir = $currentDir; // Use current directory instead of going up
            
            $output->writeln(sprintf('<comment>📂 Working directory: %s</comment>', $currentDir));
            $output->writeln('');
            
            // Check if docker-compose.yaml exists in current directory
            $dockerComposePath = $dockerDir . DIRECTORY_SEPARATOR . 'docker-compose.yaml';
            if (!file_exists($dockerComposePath)) {
                $output->writeln('<error>❌ docker-compose.yaml not found in current directory: ' . $dockerDir . '</error>');
                $output->writeln('<info>💡 Tip: Make sure Docker Compose file exists in the framework directory</info>');
                return 1;
            }
            
            $output->writeln('<success>✅ Found docker-compose.yaml</success>');
            $output->writeln('<info>🚀 Starting Docker containers...</info>');
            $output->writeln('');
            
            // Change to docker directory and run docker-compose up
            $command = sprintf('cd "%s" && docker-compose up', $dockerDir);
            
            $output->writeln('<comment>🔧 Executing: ' . $command . '</comment>');
            $output->writeln('');
            
            // Execute the docker command
            passthru($command, $returnCode);
            
            if ($returnCode === 0) {
                $output->writeln('');
                $output->writeln('<success>🎉 Docker containers started successfully!</success>');
            } else {
                $output->writeln('');
                $output->writeln('<error>❌ Failed to start Docker containers</error>');
                return $returnCode;
            }
            
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Error: ' . $e->getMessage() . '</error>');
            return 1;
        }
        
        return 0;
    }

	
	
} /* generated with antollena framework */