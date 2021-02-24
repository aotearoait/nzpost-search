<?php


namespace Aotearoait;


use GuzzleHttp\Exception\GuzzleException;

class AddressChecker extends Auth
{
    const BASE_URI = 'https://api.nzpost.co.nz/addresschecker/1.0';

    /**
     * Returns a list of suggested domestic addresses for an address fragment.
     *
     * @param $q
     * @param int $count
     * @return array|bool|float|int|object|string|null
     * @throws GuzzleException
     */

    public function addressLookup($q, $count = 1)
    {
        $uri = '/details';

        return $this->call(self::BASE_URI.$uri, [
            'q' => $q,
            'count' => $count,
        ]);
    }

}
