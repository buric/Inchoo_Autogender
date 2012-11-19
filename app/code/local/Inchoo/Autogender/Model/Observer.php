<?php

class Inchoo_Autogender_Model_Observer
{
    public function getGender($observer = null)
    {
        if($observer)
        {
            try
            {
                $customer = $observer->getCustomer();

                $api_key = 'a2c8ec18ecb90b7ada1c1473f16f9bbc';

                $client = new Zend_Http_Client();

                $client->setUri('https://personalize.rapleaf.com/v4/dr?api_key=' . $api_key .
                                '&email=' . urlencode($customer->getEmail()) .
                                '&first=' . urlencode($customer->getFirstname()) .
                                '&last=' . urlencode($customer->getLastname())
                );

                $client->setConfig(array('maxredirects' => 0, 'timeout' => 2));

                $response = $client->request();

                if ($response->getStatus() < 200 || $response->getStatus() >= 300)
                {
                    Mage::log(
                        sprintf("Rapleaf query failed. (status: %s), (message: %s)",
                            $response->getStatus(),
                            strip_tags($response->getBody())),
                        null,
                        'rapleaf_api.log',
                        false);
                }
                else
                {
                    $data = json_decode($response->getBody(), true);
                    if(array_key_exists('gender', $data))
                    {
                        $customer->setGender(
                            Mage::getResourceSingleton('customer/customer')
                                ->getAttribute('gender')
                                ->getSource()
                                ->getOptionId($data['gender'])
                        );
                    }
                }
            }
            catch (Exception $e)
            {
                Mage::log(
                    sprintf("Exception in Rapleaf query. (message: %s)",
                       strip_tags($e->getMessage())),
                    null,
                    'rapleaf_api.log',
                    false);
            }
        }
    }
}