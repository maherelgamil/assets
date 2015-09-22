<?php  namespace Maherelgamil\Asset;

use Illuminate\Support\ServiceProvider;

class AssetServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(){
        $this->publishes([
            __DIR__.'/../../Config/asset.php' => config_path('asset.php'),
        ]);
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(){

        $this->mergeConfigFrom(
            __DIR__.'/../../Config/asset.php', 'asset'
        );

        $this->createCacheFolders();


        $this->app->bind('asset', function ()
        {
            return new Asset();
        });
    }



    protected function createCacheFolders()
    {
        if(!app('files')->exists(public_path(config('asset.cache_dir'))))
        {
            app('files')->makeDirectory(config('asset.cache_dir'), $mode = 0777, true, true);
        }

        if(!app('files')->exists(public_path(config('asset.cache_dir').'/css')))
        {
            app('files')->makeDirectory(config('asset.cache_dir').'/css', $mode = 0777, true, true);
        }

        if(!app('files')->exists(public_path(config('asset.cache_dir').'/js')))
        {
            app('files')->makeDirectory(config('asset.cache_dir').'/js', $mode = 0777, true, true);
        }
    }



    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(){
        return ['asset'];
    }
}

