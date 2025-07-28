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
  *	https://symfony.com/doc/current/console
  *	https://symfony.com/doc/current/console/input.html
  *	https://symfony.com/doc/current/console/input.html#using-command-options		
  */
class Add extends BaseCommand
{
   
    // the name of the command (the part after "antonella")
    protected static $defaultName = 'add';    
    
    protected function configure()
    {
        $this->setDescription('Add Antonella`s Modules. Now only is possible add blade, dd and model')
             ->setHelp('Demonstration of custom commands created by Symfony Console component.')
             ->addArgument('module', InputArgument::REQUIRED, 'Blade, DD or Model');
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup custom styles for better visual output
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('yellow', null, ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
        
        $module = $input->getArgument('module');
        
        switch ($module) {
            case 'blade':
                return $this->AddBlade($input, $output);
            case 'dd':
                return $this->AddDD($input, $output);
            case 'model':
                return $this->AddModel($input, $output);
            default:
                $output->writeln('<error>❌ Unknown module: ' . $module . '</error>');
                $output->writeln('<info>💡 Available modules: blade, dd, model</info>');
                return 1;
        }
	}
    protected function AddBlade(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>🔧 Blade Template System Installation</info>');
        $output->writeln('   Adding powerful template engine to your project...');
        $output->writeln('');
        
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<info>❓ Do you want to install Blade template system? (y/N) </info>', false);
        
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<info>⚠️  Installation cancelled by user</info>');
            $output->writeln('<info>💡 Tip: Run "php antonella add blade" anytime to install Blade</info>');
            return 0;
        }
        
        $output->writeln('');
        $output->writeln('<info>📦 Installing jenssegers/blade via Composer...</info>');
        
        // Create progress bar for visual feedback
        $progressBar = new ProgressBar($output, 3);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Preparing installation...');
        $progressBar->start();
        
        $progressBar->advance();
        $progressBar->setMessage('Running composer require...');
        sleep(1); // Small delay for visual effect
        
        exec('composer require jenssegers/blade 2>&1', $composerOutput, $returnCode);
        
        $progressBar->advance();
        $progressBar->setMessage('Finalizing installation...');
        sleep(1);
        
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('');
        
        if ($returnCode === 0) {
            $output->writeln('<success>✅ Blade template system successfully installed!</success>');
            $output->writeln('<info>📚 You can now use Blade templates in your project</info>');
        } else {
            $output->writeln('<error>❌ Installation failed. Please check your composer configuration.</error>');
            return 1;
        }
        
        return 0;
    }
    protected function AddDD(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>🐛 Symfony Var-Dumper Installation</info>');
        $output->writeln('   Adding powerful debugging tools (dd() function)...');
        $output->writeln('');
        
        $output->writeln('<info>📦 Installing symfony/var-dumper as dev dependency...</info>');
        
        // Create progress bar for visual feedback
        $progressBar = new ProgressBar($output, 3);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Preparing installation...');
        $progressBar->start();
        
        $progressBar->advance();
        $progressBar->setMessage('Running composer require --dev...');
        sleep(1); // Small delay for visual effect
        
        exec('composer require symfony/var-dumper --dev 2>&1', $composerOutput, $returnCode);
        
        $progressBar->advance();
        $progressBar->setMessage('Finalizing installation...');
        sleep(1);
        
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('');
        
        if ($returnCode === 0) {
            $output->writeln('<success>✅ Symfony Var-Dumper successfully installed!</success>');
            $output->writeln('<info>🎯 You can now use dd() and dump() functions for debugging</info>');
            $output->writeln('<info>💡 Example: dd($variable); // Dies and dumps the variable</info>');
        } else {
            $output->writeln('<error>❌ Installation failed. Please check your composer configuration.</error>');
            return 1;
        }
        
        return 0;
    }
    
    protected function AddModel(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>🗃️ WordPress Eloquent Models Installation</info>');
        $output->writeln('   Adding powerful Eloquent ORM models for WordPress...');
        $output->writeln('');
        
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<info>❓ Do you want to install WordPress Eloquent Models? (y/N) </info>', false);
        
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<info>⚠️  Installation cancelled by user</info>');
            $output->writeln('<info>💡 Tip: Run "php antonella add model" anytime to install WordPress Eloquent Models</info>');
            return 0;
        }
        
        $output->writeln('');
        $output->writeln('<info>📦 Installing antonella-framework/wordpress-eloquent-models via Composer...</info>');
        
        // Create progress bar for visual feedback
        $progressBar = new ProgressBar($output, 3);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Preparing installation...');
        $progressBar->start();
        
        $progressBar->advance();
        $progressBar->setMessage('Running composer require...');
        sleep(1); // Small delay for visual effect
        
        exec('composer require antonella-framework/wordpress-eloquent-models 2>&1', $composerOutput, $returnCode);
        
        $progressBar->advance();
        $progressBar->setMessage('Finalizing installation...');
        sleep(1);
        
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('');
        
        if ($returnCode === 0) {
            $output->writeln('<success>✅ WordPress Eloquent Models successfully installed!</success>');
            $output->writeln('<info>📚 You can now use Eloquent ORM models for WordPress data</info>');
            $output->writeln('<comment>💡 Tip: Use models like User, Post, Comment, etc. with Eloquent syntax</comment>');
        } else {
            $output->writeln('<error>❌ Installation failed. Please check your composer configuration.</error>');
            $output->writeln('<info>💡 Make sure the package antonella-framework/wordpress-eloquent-models exists</info>');
            return 1;
        }
        
        return 0;
    }
}