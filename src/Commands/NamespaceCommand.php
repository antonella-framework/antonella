<?php
    
namespace Antonella\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 *	@see https://symfony.com/doc/current/console.html
 */
class NamespaceCommand extends BaseCommand {
	
	// the name of the command (the part after "antonella")
    protected static $defaultName = 'namespace';
	
	protected function configure()
    {
        $this->setDescription('Set a new namespace')
            ->setHelp('php antonella namespace ABCDE')
            ->addArgument('namespace', InputArgument::OPTIONAL, 'New value, CH default')
            ->addOption('namespace', null, InputOption::VALUE_NONE, 'show current namespace');
       
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup custom styles for better visual output
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        $output->getFormatter()->setStyle('comment', new OutputFormatterStyle('yellow', null, ['bold']));
        
        $output->writeln('<info>🏷️ Namespace Manager</info>');
        $output->writeln('   Managing project namespace configuration...');
        $output->writeln('');
		
		if ( !$input->getArgument('namespace') && $input->getOption('namespace'))
        {
            $namespace = $this->getNamespace();
            $output->writeln("<success>📝 Current namespace: $namespace</success>");
            $output->writeln('<info>💡 Tip: Use "php antonella namespace NEWNAME" to change it</info>');
        }
        else {
            try {
                // Generate or get new namespace
                $inputNamespace = $input->getArgument('namespace');
                $newname = sprintf('Antonella\\%1$s', strtoupper($inputNamespace) ?: $this->get_rand_letters());
                $newname_c = str_replace('\\', '\\\\', $newname);		// for composer.json
                
                $slash = DIRECTORY_SEPARATOR;
                $namespace = $this->getNamespace();
                $namespace_c = str_replace('\\', '\\\\', $namespace);	// for composer.json
                
                if ($namespace === $newname) {
                    $output->writeln('<info>⚠️  Namespace is already set to: ' . $newname . '</info>');
                    return 0;
                }
                
                $output->writeln('<comment>🔄 Starting namespace change process...</comment>');
                $output->writeln(sprintf('<info>📋 From: %s</info>', $namespace));
                $output->writeln(sprintf('<info>📋 To: %s</info>', $newname));
                $output->writeln('');
                
                // Create progress bar
                $progressBar = new ProgressBar($output, 4);
                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
                $progressBar->setMessage('Preparing namespace change...');
                $progressBar->start();
                
                // Step 1: Update composer.json
                $progressBar->setMessage('Updating composer.json...');
                $composerFile = $this->getDirBase().$slash.'composer.json';
                if (!file_exists($composerFile)) {
                    $progressBar->finish();
                    $output->writeln('');
                    $output->writeln('<error>❌ composer.json not found!</error>');
                    return 1;
                }
                
                $composer = file_get_contents($composerFile);
                $composer = str_replace($namespace_c, $newname_c, $composer);
                file_put_contents($composerFile, $composer);
                $progressBar->advance();
                
                // Step 2: Update core files
                $progressBar->setMessage('Updating core files...');
                $files = [
                    str_replace('\\', '/', sprintf('%1$s/antonella-framework.php', $this->getDirBase())),
                    str_replace('\\', '/', sprintf('%1$s/%2$s.php', $this->getDirBase(), basename($this->getDirBase())))
                ];
                
                $coreFilesUpdated = 0;
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        $core = file_get_contents($file);
                        $core = str_replace($namespace, $newname, $core);
                        file_put_contents($file, $core);
                        $coreFilesUpdated++;
                    }
                }
                $progressBar->advance();
                
                // Step 3: Update source files
                $progressBar->setMessage('Updating source files...');
                $dirName = $this->getDirBase().$slash.'src';
                $dirName = realpath($dirName);
                if (!$dirName) {
                    $progressBar->finish();
                    $output->writeln('');
                    $output->writeln('<error>❌ Source directory not found!</error>');
                    return 1;
                }
                
                if (substr($dirName, -1) != '/') {
                    $dirName .= $slash;
                }
                
                $filesUpdated = 0;
                $dirStack = [$dirName];
                while (!empty($dirStack)) {
                    $currentDir = array_pop($dirStack);
                    $dir = dir($currentDir);
                    while (false !== ($node = $dir->read())) {
                        if (($node == '..') || ($node == '.')) {
                            continue;
                        }
                        if (is_dir($currentDir.$node)) {
                            array_push($dirStack, $currentDir.$node.$slash);
                        }
                        if (is_file($currentDir.$node)) {
                            $file = file_get_contents($currentDir.$node);
                            $updatedFile = str_replace($namespace, $newname, $file);
                            if ($file !== $updatedFile) {
                                file_put_contents($currentDir.$node, $updatedFile);
                                $filesUpdated++;
                            }
                        }
                    }
                }
                $progressBar->advance();
                
                // Step 4: Regenerate autoloader
                $progressBar->setMessage('Regenerating autoloader...');
                $composerOutput = [];
                $returnCode = 0;
                exec('composer dump-autoload 2>&1', $composerOutput, $returnCode);
                $progressBar->advance();
                
                $progressBar->finish();
                $output->writeln('');
                $output->writeln('');
                
                if ($returnCode === 0) {
                    $output->writeln('<success>✅ Namespace successfully changed!</success>');
                    $output->writeln(sprintf('<success>🎯 New namespace: %s</success>', $newname));
                    $output->writeln(sprintf('<info>📁 Core files updated: %d</info>', $coreFilesUpdated));
                    $output->writeln(sprintf('<info>📄 Source files updated: %d</info>', $filesUpdated));
                    $output->writeln('<info>🔄 Autoloader regenerated successfully</info>');
                } else {
                    $output->writeln('<error>❌ Namespace changed but autoloader regeneration failed</error>');
                    $output->writeln('<info>💡 Try running "composer dump-autoload" manually</info>');
                    return 1;
                }
                
            } catch (\Exception $e) {
                $output->writeln('<error>❌ Error during namespace change: ' . $e->getMessage() . '</error>');
                return 1;
            }
        }
	}
	
	/** 
	 * devuelve una cadena aleatoria de longitud $length 
	 *		@param $length int Longitud de la cadena
	 */
	private function get_rand_letters($length = 5) {
		
		if ($length > 0) {
            $rand_id = '';
            for ($i = 1; $i <= $length; ++$i) {
                mt_srand((float) microtime() * 1000000);
                $num = mt_rand(1, 26);
                $rand_id .= $this->assign_rand_value($num);
            }
        }

        return $rand_id;
		
	}
	
	// asigna a un numero una letra y lo devuelve
	private function assign_rand_value($num)
    {
        // accepts 1 - 26
        switch ($num) {
            case '1': $rand_value = 'A'; break;
            case '2': $rand_value = 'B'; break;
            case '3': $rand_value = 'C'; break;
            case '4': $rand_value = 'D'; break;
            case '5': $rand_value = 'E'; break;
            case '6': $rand_value = 'F'; break;
            case '7': $rand_value = 'G'; break;
            case '8': $rand_value = 'H'; break;
            case '9': $rand_value = 'I'; break;
            case '10': $rand_value = 'J'; break;
            case '11': $rand_value = 'K'; break;
            case '12': $rand_value = 'L'; break;
            case '13': $rand_value = 'M'; break;
            case '14': $rand_value = 'N'; break;
            case '15': $rand_value = 'O'; break;
            case '16': $rand_value = 'P'; break;
            case '17': $rand_value = 'Q'; break;
            case '18': $rand_value = 'R'; break;
            case '19': $rand_value = 'S'; break;
            case '20': $rand_value = 'T'; break;
            case '21': $rand_value = 'U'; break;
            case '22': $rand_value = 'V'; break;
            case '23': $rand_value = 'W'; break;
            case '24': $rand_value = 'X'; break;
            case '25': $rand_value = 'Y'; break;
            case '26': $rand_value = 'Z'; break;
        }

        return $rand_value;
    }
	
} /* generated with antollena framework */