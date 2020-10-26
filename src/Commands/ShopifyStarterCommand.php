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

    private $projectName;

    private $composerPackages = [
        'esc/shopify:v2.x-dev',
    ];

    private $npmDevPackages = [
        '@eastsideco/polaris-vue@^0.1.19',
        'evee',
        'vue',
        'vue-router',
        'vue-template-compiler',
        'vuex',
    ];

    const ANSWER_YES = 'Yes';

    const ANSWER_NO = 'Nope';

    const DEFAULT_ANSWER_INDEX = 0;

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
        $this->projectName = $this->ask('What is your project name?');

        $this->installComposerPackages = $this->choice(
            'Install Composer Packages?',
            [self::ANSWER_YES, self::ANSWER_NO],
            self::DEFAULT_ANSWER_INDEX
        );

        $this->installNpmPackages = $this->choice(
            'Install NPM Packages?',
            [self::ANSWER_YES, self::ANSWER_NO],
            self::DEFAULT_ANSWER_INDEX
        );

        $this->performSetupSteps();
    }

    private function performSetupSteps()
    {
        $this->attemptComposerPackageInstalls();
        $this->addRequriedProviders();
        $this->publishEscAssets();
        $this->fixEscShopsTableMigration();
        $this->attemptNpmPackageInstalls();
        $this->scaffoldEscTestCase();
    }

    private function attemptComposerPackageInstalls()
    {
        if ($this->installComposerPackages === self::ANSWER_NO) return;

        collect($this->composerPackages)->each(function ($package) {
            $process = new Process(
                ['composer', 'require', $package]
            );
            $process->run();

            $this->info($process->getOutput());
        });
    }

    private function addRequriedProviders()
    {
        $path_to_file = config_path('app.php');
        $file_contents = file_get_contents($path_to_file);

        if (strpos($file_contents, 'Esc\Shopify\Providers\APIServiceProvider::class') !== false) {
            return;
        }

        $file_contents = str_replace(
            "DavidPeach\ShopifyStarter\ShopifyStarterServiceProvider::class,",
            "DavidPeach\ShopifyStarter\ShopifyStarterServiceProvider::class,\n        Esc\Shopify\Providers\APIServiceProvider::class,",
            $file_contents
        );
        file_put_contents($path_to_file, $file_contents);
    }

    private function publishEscAssets(){
        $process = new Process(
                ['php', 'artisan', 'vendor:publish', '--provider', 'Esc\Shopify\Providers\APIServiceProvider']
            );
            $process->run();

            $this->info($process->getOutput());
    }

    private function fixEscShopsTableMigration()
    {
        $path_to_file = database_path('migrations/2016_07_03_000000_create_shops_table.php');
        $file_contents = file_get_contents($path_to_file);

        if (strpos($file_contents, "table->integer('user_id')->unsigned()") === false) {
            return;
        }

        $file_contents = str_replace(
            "table->integer('user_id')->unsigned()",
            "table->unsignedBigInteger('user_id')",
            $file_contents
        );

        file_put_contents($path_to_file, $file_contents);
    }

    private function attemptNpmPackageInstalls()
    {
        if ($this->installNpmPackages === self::ANSWER_NO) return;

        collect($this->npmDevPackages)->each(function ($package) {
            $process = new Process(
                ['npm', 'install', '--save-dev', $package]
            );
            $process->run();
            $this->info($process->getOutput());
        });
    }

    private function scaffoldEscTestCase()
    {
        if (file_exists($path = base_path('tests/EscTestCase.php'))) {
            return;
        }

        file_put_contents(
            $path,
            file_get_contents(__DIR__ . '/../stubs/EscTestCase.stub')
        );
    }
}
