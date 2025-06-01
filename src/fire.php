<?php

/**
 * .oooooo..o                                                                ooooo      ooo ooooo 
 *d8P'    `Y8                                                                `888'      `8' `888' 
 *Y88bo.      oooo   ooo  ooo. .oo.    .oooo.   oo.ooooo.  .oooo.o  .ooooo.   888        8   888  
 *`"Y8888o.   `88.  .8'  `888P"Y88b  `P  )88b   888' `88b d88(  "8 d88' `88b  888        8   888  
 *    `"Y88b   `88..8'    888   888   .oP"888   888   888 `"Y88b.  888ooo888  888        8   888  
 *oo    .d8P    `888'     888   888  d8(  888   888   888 o.  )88b 888    .o  `88.     .8'   888  
 *8""88888P'     .8'     o888o o888o `Y888""8o  888bod8P' 8""888P' `Y8bod8P'     `YbodP'     o888o 
 *           .o..P'                             888                                              
 *           `Y8P'                             o888o                                             
 * ===================================================================================================     
 *  
 * The maker of this software: Par274
 * Author: https://www.r10.net/members/90047-scarecrow.html | https://github.com/par274 
 * The software uses ParFramework2, this infrastructure cannot be used by third parties or any other project can not be produced. !!                                                                                         
 */

//Root Dir
define('ROOT_DIR', __DIR__);

//Native platform dir
define('NATIVE_PLATFORM_DIR', ROOT_DIR . '/platform/Native');

//Web platform dir
define('WEB_PLATFORM_DIR', ROOT_DIR . '/platform/Web2');

//Internal dir
define('INTERNAL_DIR', ROOT_DIR . '/internal');

return (function (): \PlatformBridge\BridgeFoundation|bool
{
    if (!file_exists(ROOT_DIR . '/vendor/autoload.php'))
    {
        throw new \Exception("Autoloader file not found, please run 'composer update' command on root dir.");

        return false;
    }

    require(ROOT_DIR . '/vendor/autoload.php');

    if (!file_exists(ROOT_DIR . '/.env'))
    {
        throw new \Exception(".env config file not found.");

        return false;
    }

    /**
     * Parse environment config file
     */
    $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_DIR, ['.env'], false);
    $dotenv->load();

    /**
     * Run: Open bridge native and web application.
     */
    $bridge = new \PlatformBridge\BridgeFoundation();
    $bridge->run();

    return $bridge;
})();
