<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class UsefulToolsHelper
{
    /**
     * Returns string in JSON format with flag success or not, message, error code and JSON array with results.
     *
     * @param mixed  $results The result data of the request
     * @param string $message A message passed back
     *
     * @return JsonResponse The JsonResponse object with flag success or not, message, error code and results.
     */
    public function generate_answer(mixed $results = '', string $message = '', string $error_code = '', $http_status = 200)
    {
        if (200 != $http_status) {
            $results = [
                'error_code' => $error_code,
                'message' => $message,
            ];
        }

        return new JsonResponse($results, $http_status, []);
    }

    /**
     * Validates array of values like user name, password etc. received via a request.
     *
     * @param array $values The array with values to validate
     *
     * @return string Empty string if all values in array are valid, a name of wrong value otherwise
     */
    public function validateCredentials(array $values): string
    {
        // Validate user name
        if (isset($values['user_name']) && !preg_match('/^[A-Za-z0-9]{3,128}+$/', $values['user_name'])) {
            return 'user_name';
        }

        // Validate login name
        if (isset($values['login']) && !preg_match('/^[A-Za-z0-9]{3,128}+$/', $values['login'])) {
            return 'login';
        }

        // Validate password
        if (isset($values['password']) && !preg_match('/^[A-Za-z0-9!%$#&_\-]{6,128}+$/', $values['password'])) {
            return 'password';
        }

        // Validate country
        if (isset($values['country']) && !preg_match('/^[A-Za-z]{2,2}+$/', $values['country'])) {
            return 'country';
        }

        // Validate ip
        if (isset($values['ip']) && !preg_match('/^[0-9\.]{6,20}+$/', $values['ip'])) {
            return 'ip';
        }

        // Validate wallet_address
        if (isset($values['wallet_address']) && !preg_match('/^0x[0-9a-zA-Z]{40,42}+$/', $values['wallet_address'])) {
            return 'wallet_address';
        }

        // Validate email
        if (isset($values['email']) && !preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i', $values['email'])) {
            return 'email';
        }

        // Validate picture
        if (isset($values['picture'])) {
            $max_size_user_picture = 1024 * 1024 * 10;
            if (isset($_ENV['MAX_SIZE_USER_PICTURE'])) {
                $max_size_user_picture = $_ENV['MAX_SIZE_USER_PICTURE'];
            }

            if (mb_strlen($values['picture']) > $max_size_user_picture) {
                return "Maximum picture size is: $max_size_user_picture bytes";
            }

            if (!preg_match('/data:image(.*)/i', $values['picture'])) {
                return 'picture';
            }
        }

        return '';
    }

    /**
     * Return sanitzed safe string.
     *
     * @param string $values A string that will be sanitized
     *
     * @return string
     */
    static function sanitizeString(
        $string,
        $max_length = 0,
        $allow_html = false,
        $only_standard_chars = false,
        $replace_non_standard_chars = '<br>',
        $replace_quotes = true,
        $remove_quotes = false,
        $its_unicode = false,
        $preg_remove = ''
    ) {
        if ($only_standard_chars) {
            for ($i = 0; $i < mb_strlen($string); ++$i) {
                if ((int) \ord($string[$i]) < (int) \ord(' ') || (int) \ord($string[$i]) > (int) \ord('~')) {
                    $string[$i] = \chr(1);
                }
            }
            $string = str_replace(\chr(1), $replace_non_standard_chars, $string);
        }

        $string = str_replace('\\', '&#92;', $string);
        if ($replace_quotes) {
            $string = preg_replace('/"/', '&quot;', $string);
            $string = preg_replace('/\'/', '&#39;', $string);
        }

        if ($remove_quotes) {
            $string = preg_replace('/"/', '', $string);
            $string = preg_replace('/\'/', '', $string);
        }

        if (!empty($preg_remove)) {
            $string = preg_replace($preg_remove, '', $string);
        }

        if (!$allow_html) {
            $string = str_replace('<', '&lt;', $string);
            $string = str_replace('>', '&gt;', $string);
        }

        if ($max_length > 0) {
            if ($its_unicode) {
                $string = mb_substr($string, 0, $max_length, 'HTML-ENTITIES');
            } else {
                $string = mb_substr($string, 0, $max_length);
            }
        }

        return $string;
    }

    /**
     * Convert a string to camelCase.
     *
     * @param string $values A string that will be converted
     *
     * @return string
     */
    public function camelCase(
        string $string
    ) {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }
}
