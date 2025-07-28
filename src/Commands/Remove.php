<?php

namespace Antonella\Commands;
 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
 
/**
  * @see https://code.tutsplus.com/es/tutorials/how-to-create-custom-cli-commands-using-the-symfony-console-component--cms-31274
  *		 https://symfony.com/doc/current/console
  *		 https://symfony.com/doc/current/console/input.html
  *		 https://symfony.com/doc/current/console/input.html#using-command-options		
  */
 class Remove extends BaseCommand
{
    
    // the name of the command (the part after "antonella")
    protected static $defaultName = 'remove';

    protected $understant = '<comment>Antonella no understand you. please read the manual in https://antonellaframework.com</comment>';
    
    protected function configure()
    {
        $this->setDescription('Remove Antonella`s Modules. Now only is possible remove blade, dd and model')
             ->setHelp('Demonstration of custom commands created by Symfony Console component.')
             ->addArgument('module', InputArgument::REQUIRED, 'Blade, DD or Model');																		// OPTIONAL [--color=your-color] --or
																							//			[--color your-color]
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup custom styles for better visual output
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        
        $module = $input->getArgument('module');
        
        switch ($module) {
            case 'blade':
                return $this->RemoveBlade($input, $output);
            case 'dd':
                return $this->RemoveDD($input, $output);
            case 'model':
                return $this->RemoveModel($input, $output);
            default:
                $output->writeln('<error>❌ Unknown module: ' . $module . '</error>');
                $output->writeln('<info>💡 Available modules: blade, dd, model</info>');
                return 1;
        }
	}
    protected function RemoveDD(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>🗑️ Symfony Var-Dumper Removal</info>');
        $output->writeln('   Removing debugging tools (dd() function)...');
        $output->writeln('');
        
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<info>❓ Are you sure you want to remove Symfony Var-Dumper? (y/N) </info>', false);
        
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<info>⚠️  Removal cancelled by user</info>');
            $output->writeln('<info>💡 Tip: Run "php antonella remove dd" anytime to remove Var-Dumper</info>');
            return 0;
        }
        
        $output->writeln('');
        $output->writeln('<info>📦 Removing symfony/var-dumper via Composer...</info>');
        
        // Create progress bar for visual feedback
        $progressBar = new ProgressBar($output, 3);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Preparing removal...');
        $progressBar->start();
        
        $progressBar->advance();
        $progressBar->setMessage('Running composer remove...');
        sleep(1);
        
        exec('composer remove symfony/var-dumper 2>&1', $composerOutput, $returnCode);
        
        $progressBar->advance();
        $progressBar->setMessage('Finalizing removal...');
        sleep(1);
        
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('');
        
        if ($returnCode === 0) {
            $output->writeln('<success>✅ Symfony Var-Dumper successfully removed!</success>');
            $output->writeln('<info>🧹 dd() and dump() functions are no longer available</info>');
        } else {
            $output->writeln('<error>❌ Removal failed. Please check your composer configuration.</error>');
            return 1;
        }
        
        return 0;
    }
    protected function RemoveBlade(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>🗑️ Blade Template System Removal</info>');
        $output->writeln('   Removing template engine from your project...');
        $output->writeln('');
        
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<info>❓ Are you sure you want to remove Blade template system? (y/N) </info>', false);
        
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<info>⚠️  Removal cancelled by user</info>');
            $output->writeln('<info>💡 Tip: Run "php antonella remove blade" anytime to remove Blade</info>');
            return 0;
        }
        
        $output->writeln('');
        $output->writeln('<info>📦 Removing jenssegers/blade via Composer...</info>');
        
        // Create progress bar for visual feedback
        $progressBar = new ProgressBar($output, 3);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Preparing removal...');
        $progressBar->start();
        
        $progressBar->advance();
        $progressBar->setMessage('Running composer remove...');
        sleep(1);
        
        exec('composer remove jenssegers/blade 2>&1', $composerOutput, $returnCode);
        
        $progressBar->advance();
        $progressBar->setMessage('Finalizing removal...');
        sleep(1);
        
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('');
        
        if ($returnCode === 0) {
            $output->writeln('<success>✅ Blade template system successfully removed!</success>');
            $output->writeln('<info>🧹 Blade templates are no longer available in your project</info>');
        } else {
            $output->writeln('<error>❌ Removal failed. Please check your composer configuration.</error>');
            return 1;
        }
        
        return 0;
    }
    
    protected function RemoveModel(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>🗃️ WordPress Eloquent Models Removal</info>');
        $output->writeln('   Removing Eloquent ORM models for WordPress...');
        $output->writeln('');
        
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<info>❓ Are you sure you want to remove WordPress Eloquent Models? (y/N) </info>', false);
        
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<info>⚠️  Removal cancelled by user</info>');
            $output->writeln('<info>💡 Tip: Run "php antonella remove model" anytime to remove WordPress Eloquent Models</info>');
            return 0;
        }
        
        $output->writeln('');
        $output->writeln('<info>📦 Removing antonella-framework/wordpress-eloquent-models via Composer...</info>');
        
        // Create progress bar for visual feedback
        $progressBar = new ProgressBar($output, 3);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Preparing removal...');
        $progressBar->start();
        
        $progressBar->advance();
        $progressBar->setMessage('Running composer remove...');
        sleep(1); // Small delay for visual effect
        
        exec('composer remove antonella-framework/wordpress-eloquent-models 2>&1', $composerOutput, $returnCode);
        
        $progressBar->advance();
        $progressBar->setMessage('Finalizing removal...');
        sleep(1);
        
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('');
        
        if ($returnCode === 0) {
            $output->writeln('<success>✅ WordPress Eloquent Models successfully removed!</success>');
            $output->writeln('<info>🧹 Eloquent ORM models are no longer available in your project</info>');
            $output->writeln('<comment>💡 Tip: You can reinstall anytime with "php antonella add model"</comment>');
        } else {
            $output->writeln('<error>❌ Removal failed. Please check your composer configuration.</error>');
            $output->writeln('<info>💡 Make sure the package antonella-framework/wordpress-eloquent-models is installed</info>');
            return 1;
        }
        
        return 0;
    }
}