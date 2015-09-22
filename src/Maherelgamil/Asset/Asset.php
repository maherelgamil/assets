<?php namespace Maherelgamil\Asset;

use Maherelgamil\Asset\Models\Compressor;

class Asset
{


    /**
     * @var Compressor
     */
    protected  $compressor;



    protected $minify;

    /**
     * @var array
     */
    protected $styles = [];


    /**
     * @var array
     */
    protected $scripts = [];



    public function __construct(Compressor $compressor)
    {
        $this->config = config('asset');
        $this->compressor = $compressor;
    }

    public function add($asset , $main = false )
    {
        $file = public_path($asset) ;

        $this->checkIfFileExists($file);

        if($this->assetExtension($asset) == 'css')
        {
            if($main)
            {
                $this->styles['main'][] =  $asset;
            }
            else
            {
                $this->styles['slave'][] = $asset ;
            }
        }
        elseif($this->assetExtension($asset) == 'js')
        {
            if($main)
            {
                $this->scripts['main'][] =  $asset;
            }
            else
            {
                $this->scripts['slave'][] = $asset ;
            }
        }
    }

    public function styles()
    {
        $styles = '';

        if(isset($this->styles['main']))
        {
            foreach($this->styles['main'] as $key => $style)
            {
                if(config('asset.mix'))
                {
                    $stylesArrayForMixxing[] = $style ;
                }
                else
                {
                    $styles .= '<link rel="stylesheet" href="'.$this->asset($style).'" />';
                }
            }
        }


        if(isset($this->styles['slave']))
        {
            foreach($this->styles['slave'] as $key => $style)
            {
                if(config('asset.mix'))
                {
                    $stylesArrayForMixxing[] = $style ;
                }
                else
                {
                    $styles .= '<link rel="stylesheet" href="'.$this->asset($style).'" />';
                }
            }
        }

        if(isset($stylesArrayForMixxing))
        {
            $styles = '<link rel="stylesheet" href="'.$this->assets($stylesArrayForMixxing , 'css').'" />';
        }


        return $styles ;
    }

    public function scripts()
    {
        $scripts = '';

        if(isset($this->scripts['main']))
        {
            foreach($this->scripts['main'] as $key => $script)
            {
                if(config('asset.mix'))
                {
                    $scriptsArrayForMixxing[] = $script ;
                }
                else
                {
                    $scripts .= '<script src="'.$this->asset($script).'"></script>';
                }
            }
        }

        if(isset($this->scripts['slave']))
        {
            foreach($this->scripts['slave'] as $key => $script)
            {
                if(config('asset.mix'))
                {
                    $scriptsArrayForMixxing[] = $script ;
                }
                else
                {
                    $scripts .= '<script src="'.$this->asset($script).'"></script>';
                }
            }
        }


        if(isset($scriptsArrayForMixxing))
        {
            $scripts = '<script src="'.$this->assets($scriptsArrayForMixxing , 'js').'"></script>';
        }

        return $scripts ;
    }

    protected function assetExtension($filename)
    {
        $path_info = pathinfo($filename);
        return $path_info['extension'];
    }

    protected function checkIfFileExists($file)
    {
        return file_exists($file) ? true : abort(500 , 'File: `'.$file.'` Not Exists') ;
    }

    protected function asset($asset)
    {
        return config('asset.min') ? $this->minify($asset) : asset($asset) ;
    }

    protected function assets($assets , $type )
    {

        $mix_asset = config('asset.cache_dir').'/'.$type.'/assets-'.$this->cacheBustedGenerator($assets).'.'.$type;

        if(!$this->checkAssetsModifiedForGroup($mix_asset , $assets))
        {
            $assetsArray = [];
            foreach($assets as $asset)
            {
                $assetsArray[] = public_path($asset) ;
            }



            $content = '';
            foreach($assetsArray as $assetPasth)
            {
                $content .= app('files')->get($assetPasth);
            }


            if(config('asset.min'))
            {
                $content = $this->compressor->compress($content , $type);
            }


            app('files')->put(public_path($mix_asset) ,$content);

            $this->copyAssetsFolders();
        }

        return asset($mix_asset);

    }

    protected function minify($asset)
    {
        //return if no modified in original files
        if($this->checkAssetsModified($asset))
        {
            return $this->getCacheAsset($asset);
        }

        $this->compressRender($asset);


        //return new asset cache
        return $this->getCacheAsset($asset);

    }

    protected function compressRender($asset)
    {
        $content = app('files')->get(public_path($asset));
        $compressContent = $this->compressor->compress($content , $this->assetExtension($asset) );
        app('files')->put($this->getCachePath($asset) ,$compressContent);

        $this->copyAssetsFolders();
    }

    protected function copyAssetsFolders()
    {
        app('files')->copyDirectory(config('asset.images_dir'), public_path(config('asset.cache_dir').'/images'));
        app('files')->copyDirectory(config('asset.fonts_dir'), public_path(config('asset.cache_dir').'/fonts'));
    }

    protected function getCachePath($asset)
    {
        return public_path($this->getCacheAssetUrl($asset));
    }

    protected function getCacheAsset($asset)
    {
        return asset($this->getCacheAssetUrl($asset));
    }

    protected function getCacheAssetUrl($asset)
    {
        $file = public_path($asset);
        $path_info = pathinfo($file);

        return $path_info['extension'].'/'.$path_info['filename'].'-'.$this->cacheBustedGenerator($asset).'.'.$path_info['extension'] ;
    }

    protected function cacheBustedGenerator($assets)
    {
        $file = '';
        $bust = '';

        if(!is_array($assets))
        {
            $file   = public_path($assets);
            $bust   = md5(filemtime($file));
        }
        else
        {
            foreach($assets as $asset)
            {
                $assetFile   = public_path($asset);

                $file   .= public_path($asset);
                $bust   .= md5(filemtime($assetFile));
            }
        }

        return md5($file.$bust);
    }

    protected function getCacheBusterfromOldFile($asset)
    {
        $name_parameters = explode('-' , $this->getCachePath($asset));
        $old_bust = explode('.', $name_parameters[1])[0];

        return $old_bust ;
    }

    protected function checkAssetsModified($asset)
    {
        if(!app('files')->exists($asset))
        {
            return false ;
        }

        //get old bust
        $old_bust = $this->getCacheBusterfromOldFile($asset);

        //get new bust
        $new_bust = $this->cacheBustedGenerator($asset);


        return $old_bust == $new_bust ? true : false ;
    }

    protected function checkAssetsModifiedForGroup($asset , $assets)
    {
        if(!app('files')->exists($asset))
        {
            return false ;
        }

        //get old bust
        $old_bust = $this->getCacheBusterfromOldFile($asset);

        //get new bust
        $new_bust = $this->cacheBustedGenerator($assets);


        return $old_bust == $new_bust ? true : false ;
    }



}