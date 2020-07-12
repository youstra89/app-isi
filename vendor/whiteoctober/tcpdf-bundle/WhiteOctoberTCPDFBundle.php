<?php

namespace WhiteOctober\TCPDFBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Filesystem\Filesystem;

class WhiteOctoberTCPDFBundle extends Bundle
{
    /**
     * Ran on bundle boot, our TCPDF configuration constants
     * get defined here if required
     */
    public function boot()
    {
        if (!$this->container->hasParameter('white_october_tcpdf.tcpdf')) {
            return;
        }

        // Define our TCPDF variables
        $config = $this->container->getParameter('white_october_tcpdf.tcpdf');

        // TCPDF needs some constants defining if our configuration
        // determines we should do so (default true)
        // Set tcpdf.k_tcpdf_external_config to false to use the TCPDF
        // core defaults
        if ($config['k_tcpdf_external_config'])
        {
            foreach ($config as $k => $v)
            {
                $constKey = strtoupper($k);
                
                if (!defined($constKey))
                {
                	$value = $this->container->getParameterBag()->resolveValue($v);
                	
                	// All K_ constants are required
                	if (preg_match("/^k_/i", $k))
                	{
                    	
                        if (($k === 'k_path_cache' || $k === 'k_path_url_cache') && !is_dir($value)) {
                            $this->createDir($value);
                        }

                        if(in_array($constKey, ['K_PATH_URL','K_PATH_MAIN','K_PATH_FONTS','K_PATH_CACHE','K_PATH_URL_CACHE','K_PATH_IMAGES'])) {
                            $value .= (substr($value, -1) == '/' ? '' : '/');
                        }

                    }
                    define($constKey, $value);
                }
            }
        }
    }

    /**
     * Create a directory
     *
     * @param string $filePath
     *
     * @throws \RuntimeException
     */
    private function createDir($filePath)
    {
        $filesystem = new Filesystem();
        if (false === $filesystem->mkdir($filePath)) {
            throw new \RuntimeException(sprintf(
                'Could not create directory %s', $filePath
            ));
        }
    }
}
