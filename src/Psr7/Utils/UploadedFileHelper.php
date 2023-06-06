<?php 
/**
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Psr7\Utils;

use Shieldon\Psr7\UploadedFile;

use function array_merge_recursive;
use function rtrim;
use function is_array;
use function is_string;
use function is_numeric;

/*
 * The helper functions for converting $_FILES to an array of UploadedFile
 * instance, only used on ServerRequest class.
 * This class is not a part of PSR 7.
 */
class UploadedFileHelper
{
/**
     * Create an array for PSR-7 Uploaded File needed.
     * 
     * @param array $files     An array generally from $_FILES
     * @param bool  $isConvert To covert and return $files as an UploadedFile instance.
     * 
     * @return array|UploadedFile
     */
    public static function uploadedFileParse(array $files)
    {
        $specTree = [];

        $specFields = [
            0 => 'tmp_name',
            1 => 'name',
            2 => 'type',
            3 => 'size',
            4 => 'error',
        ];

        foreach ($files as $fileKey => $fileValue) {
            if (!isset($fileValue['tmp_name'])) {
                // @codeCoverageIgnoreStart
                return [];
                // @codeCoverageIgnoreEnd
            }

            if (is_string($fileValue['tmp_name']) || is_numeric($fileValue['tmp_name'])) {
                $specTree[$fileKey] = $fileValue;

            } elseif (is_array($fileValue['tmp_name'])) {

                $tmp = [];

                // We want to find out how many levels of array it has.
                foreach ($specFields as $i => $attr) {
                    $tmp[$i] = self::uploadedFileNestedFields($fileValue, $attr);
                }

                $parsedTree = array_merge_recursive(
                    $tmp[0], // tmp_name
                    $tmp[1], // name
                    $tmp[2], // type
                    $tmp[3], // size
                    $tmp[4]  // error
                );
  
                $specTree[$fileKey] = $parsedTree;
                unset($tmp, $parsedTree);
            }
        }

        return self::uploadedFileArrayTrim($specTree);
    }

    /**
     * Find out how many levels of an array it has.
     *
     * @param array  $files Data structure from $_FILES.
     * @param string $attr  The attributes of a file.
     *
     * @return array
     */
    public static function uploadedFileNestedFields(array $files, string $attr): array
    {
        $result = [];
        $values = $files;

        if (isset($files[$attr])) {
            $values = $files[$attr];
        }

        foreach ($values as $key => $value) {

            /**
             * Hereby to add `_` to be a part of the key for letting `array_merge_recursive`
             * method can deal with numeric keys as string keys. 
             * It will be restored in the next step.
             *
             * @see uploadedFileArrayTrim
             */
            if (is_numeric($key)) {
                $key .= '_';
            }

            if (is_array($value)) {
                $result[$key] = self::uploadedFileNestedFields($value, $attr);
            } else {
                $result[$key][$attr] = $value;
            }
        }

        return $result;
    }

    /**
     * That's because that PHP function `array_merge_recursive` has the different
     * results as dealing with string keys and numeric keys.
     * In the previous step, we made numeric keys to stringify, so that we want to
     * restore them back to numeric ones.
     *
     * @param array|string $values
     *
     * @return array|string
     */
    public static function uploadedFileArrayTrim($values)
    {
        $result = [];

        if (is_array($values)) {

            foreach ($values as $key => $value) {

                // Restore the keys back to the original ones.
                $key = rtrim($key, '_');

                if (is_array($value)) {
                    $result[$key] = self::uploadedFileArrayTrim($value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Convert the parse-ready array into PSR-7 specs.
     *
     * @param string|array $values
     * 
     * @return array
     */
    public static function uploadedFileSpecsConvert($values) 
    {
        $result = [];

        if (is_array($values)) {

            foreach ($values as $key => $value) {

                if (is_array($value)) {

                    // Continue querying self, until a string is found.
                    $result[$key] = self::uploadedFileSpecsConvert($value);

                } elseif ($key === 'tmp_name') {

                    /**
                     * Once one of the keys on the same level has been found,
                     * then we can fetch the others at a time.
                     * In this case, the `tmp_name` found.
                     */
                    $result = new uploadedFile(
                        $values['tmp_name'],
                        $values['name'],
                        $values['type'],
                        $values['size'],
                        $values['error']
                    );
                }
            }
        }

        return $result;
    }
}
