<?php
/**
 * This file is part of the Cloudinary PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\Test\Cloudinary;

use Cloudinary\ArrayUtils;
use Cloudinary\Asset\AssetType;
use Cloudinary\Asset\DeliveryType;
use Cloudinary\Asset\Media;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Configuration\ConfigUtils;
use Cloudinary\Configuration\UrlConfig;
use Cloudinary\Test\Unit\UnitTestCase;
use Monolog\Handler\TestHandler;

/**
 * Class AssetTestCase
 */
abstract class AssetTestCase extends UnitTestCase
{
    const ASSET_ID = 'sample';

    const IMG_EXT        = 'png';
    const IMG_EXT_JPG    = 'jpg';
    const IMG_EXT_GIF    = 'gif';
    const IMAGE_NAME     = self::ASSET_ID . '.' . self::IMG_EXT;
    const IMAGE_NAME_GIF = self::ASSET_ID . '.' . self::IMG_EXT_GIF;

    const VID_EXT    = 'mp4';
    const VIDEO_NAME = self::ASSET_ID . '.' . self::VID_EXT;

    const FILE_EXT  = 'bin';
    const FILE_NAME = self::ASSET_ID . '.' . self::FILE_EXT;

    const FOLDER          = 'test_folder';
    const IMAGE_IN_FOLDER = self::FOLDER . '/' . self::IMAGE_NAME;

    const FETCH_IMAGE_URL = 'https://cloudinary-res.cloudinary.com/image/upload/' . self::IMAGE_NAME;

    const PROTOCOL_HTTP  = 'http';
    const PROTOCOL_HTTPS = 'https';

    const TEST_HOSTNAME        = 'hello.com';
    const PRIVATE_CDN_HOSTNAME = self::CLOUD_NAME . '-res.' . UrlConfig::DEFAULT_DOMAIN;
    const CUSTOM_CLOUD_NAME    = 'custom-' . self::CLOUD_NAME;

    const TEST_SOCIAL_PROFILE_ID   = 123123;
    const TEST_SOCIAL_PROFILE_NAME = 'johndoe';
    const TEST_EMAIL               = 'info@cloudinary.com';

    const TEST_ASSET_VERSION        = 1486020273;
    const TEST_ASSET_VERSION_STR    = 'v' . self::TEST_ASSET_VERSION;
    const DEFAULT_ASSET_VERSION     = 1;
    const DEFAULT_ASSET_VERSION_STR = 'v' . self::DEFAULT_ASSET_VERSION;

    const URL_SUFFIX              = 'hello';
    const URL_SUFFIXED_ASSET_ID   = self::ASSET_ID . '/' . self::URL_SUFFIX;
    const URL_SUFFIXED_IMAGE_NAME = self::URL_SUFFIXED_ASSET_ID . '.' . self::IMG_EXT;

    const TEST_LOGGING = ['logging' => ['test' => ['level' => 'debug']]];

    public function setUp()
    {
        parent::setUp();

        $config = ConfigUtils::parseCloudinaryUrl(getenv(Configuration::CLOUDINARY_URL_ENV_VAR));
        $config = array_merge($config, self::TEST_LOGGING);
        Configuration::instance()->init($config);
    }

    public function tearDown()
    {
        parent::tearDown();

        Configuration::instance()->init();
    }

    /**
     * @param        $expectedPath
     * @param        $actualUrl
     * @param array  $options
     */
    protected static function assertAssetUrl(
        $expectedPath,
        $actualUrl,
        $options = []
    ) {
        $message    = ArrayUtils::get($options, 'message');
        $protocol   = ArrayUtils::get($options, 'protocol', 'https');
        $hostname   = ArrayUtils::get($options, 'hostname', UrlConfig::DEFAULT_SHARED_HOST);
        $cloudName  = ArrayUtils::get($options, 'cloud_name', self::CLOUD_NAME);
        $privateCdn = ArrayUtils::get($options, 'private_cdn', false);

        if (! $privateCdn) {
            $hostAndCloud = ArrayUtils::implodeUrl([$hostname, $cloudName]);
        } else {
            $hostAndCloud = ArrayUtils::implodeFiltered('-', [$cloudName, $hostname]);
        }

        $suffix = ArrayUtils::get($options, 'suffix', '');

        $expectedUrl = "$protocol://$hostAndCloud/$expectedPath$suffix";

        self::assertEquals($expectedUrl, (string)$actualUrl, $message);
    }

    /**
     * @param        $expectedPath
     * @param        $actualUrl
     * @param array  $options
     */
    protected static function assertImageUrl(
        $expectedPath,
        $actualUrl,
        $options = []
    ) {
        $deliveryType = ArrayUtils::get($options, 'delivery_type', DeliveryType::UPLOAD);

        $expectedPath = AssetType::IMAGE . "/$deliveryType/$expectedPath";

        self::assertAssetUrl($expectedPath, $actualUrl, $options);
    }

    /**
     * @param        $expectedPath
     * @param        $actualUrl
     * @param null   $message
     * @param string $customDeliveryType
     */
    protected static function assertVideoUrl(
        $expectedPath,
        $actualUrl,
        $message = null,
        $customDeliveryType = DeliveryType::UPLOAD
    ) {
        $expectedPath = AssetType::VIDEO . "/$customDeliveryType/$expectedPath";

        self::assertAssetUrl($expectedPath, $actualUrl, $message);
    }

    /**
     * @param        $expectedPath
     * @param        $actualUrl
     * @param null   $message
     * @param string $customDeliveryType
     */
    protected static function assertFileUrl(
        $expectedPath,
        $actualUrl,
        $message = null,
        $customDeliveryType = DeliveryType::UPLOAD
    ) {
        $expectedPath = AssetType::RAW . "/$customDeliveryType/$expectedPath";

        self::assertAssetUrl($expectedPath, $actualUrl, $message);
    }

    /**
     * @param       $source
     * @param array $params
     * @param array $expectationOptions
     */
    protected static function assertMediaFromParamsUrl(
        $source,
        $params = [],
        $expectationOptions = []
    ) {
        $actualUrl = Media::fromParams($source, $params);

        ArrayUtils::setDefaultValue($expectationOptions, 'protocol', 'http');

        $path = ArrayUtils::get($expectationOptions, 'path');

        $assetType    = ArrayUtils::get($expectationOptions, 'asset_type', AssetType::IMAGE);
        $deliveryType = ArrayUtils::get($expectationOptions, 'delivery_type', DeliveryType::UPLOAD);

        $actualSource = ArrayUtils::get($expectationOptions, 'source', $source);

        // Expected full path overrides everything
        $expectedPath = ArrayUtils::get(
            $expectationOptions,
            'full_path',
            "$assetType/$deliveryType/" . ArrayUtils::implodeUrl([$path, $actualSource])
        );

        self::assertAssetUrl($expectedPath, $actualUrl, $expectationOptions);
    }

    /**
     * Asserts that a given object logged a message of a certain level
     *
     * @param object     $obj     The object that should have logged a message
     * @param string     $message The message that was logged
     * @param string|int $level   Logging level value or name
     */
    protected static function assertObjectLoggedMessage($obj, $message, $level)
    {
        $reflectionMethod = new \ReflectionMethod(get_class($obj), 'getLogger');
        $reflectionMethod->setAccessible(true);
        $logger = $reflectionMethod->invoke($obj);

        self::assertLoggerHasRecordThatContains($logger->getTestHandler(), $message, $level);
    }

    /**
     * Asserts that a given Logger TestHandler has logged a message of a certain level
     *
     * @param TestHandler $testHandler The TestHandler to inspect
     * @param string      $message     The message that was logged
     * @param string|int  $level       Logging level value or name
     */
    protected static function assertLoggerHasRecordThatContains($testHandler, $message, $level)
    {
        self::assertInstanceOf(TestHandler::class, $testHandler);
        self::assertTrue($testHandler->hasRecordThatContains($message, $level));
    }

    protected function assertErrorThrowing(callable $function)
    {
        $errorsThrown = 0;
        set_error_handler(
            function () use (&$errorsThrown) {
                $errorsThrown++;

                return true;
            }
        );
        $function();
        restore_error_handler();
        $this->assertEquals(1, $errorsThrown, 'Failed assert that error was thrown');
    }
}
