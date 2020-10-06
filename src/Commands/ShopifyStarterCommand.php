<?php

namespace DavidPeach\ShopifyStarter\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ShopifyStarterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'davidpeach:shopify-app-setup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projectName = $this->ask('What is your project name?');

        $installNpmPackages = $this->choice(
            'Install NPM Packages?',
            ['Yes, please', 'Nah, fam.'],
            0
        );

        $npmDevPackages = collect([
            '@eastsideco/polaris-vue@^0.1.19',
            'evee',
            'vue',
            'vue-router',
            'vue-template-compiler',
            'vuex',
        ]);

        if ($installNpmPackages) {
            $npmDevPackages->each(function ($package) {
                $process = new Process(
                    ['npm', 'install', '--save-dev', $package]
                );
                $process->run();
                $this->info($process->getOutput());
            });
        }
    }
}
