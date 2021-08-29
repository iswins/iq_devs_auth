<?php
/**
 * Created by v.taneev.
 */


namespace App\Clients;


class DaDataClient extends AbstractRestClient
{
    protected function getServiceName ()
    {
        return 'dadata';
    }

    public function getCompanyByInn($inn) {
        $url = "/api/company/inn/{$inn}/";
        return $this->get($url);
    }
}
