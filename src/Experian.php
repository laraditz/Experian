<?php

namespace Laraditz\Experian;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Laraditz\Experian\Models\ExperianRecord;
use Laraditz\Experian\Models\ExperianRequest;
use LogicException;

class Experian
{
    private bool $sandboxMode = false;
    private string $baseUrl;
    private string $method = 'post';
    private string $action = '';

    public function __construct(
        private ?string $vendor = null,
        private ?string $username = null,
        private ?string $password = null
    ) {
        $this->setVendor($vendor ?? config('experian.vendor'));
        $this->setUsername($username ?? config('experian.username'));
        $this->setPassword($password ?? config('experian.password'));
        $this->setSandboxMode(config('experian.sandbox.mode'));
        $this->setBaseUrl();
    }

    public function ccrisSearch(
        string $name,
        string $id,
        string $dob,
        string $country = 'MY',
        ?string $id2 = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $address = null
    ) {
        $data = [
            'ProductType' => 'CCRIS_SEARCH',
            'GroupCode' => '11',
            'EntityName' => $name,
            'EntityId' => $id,
            'Country' => $country,
            'DOB' => $dob,
            'SpgaId' => $id,
            'IDCode' => 'C',
        ];

        if ($id2) {
            $data['EntityId2'] = $id2;
        }

        $response = $this->method('post')
            ->action('ccrisSearch')
            ->makeRequest('report', $data);

        throw_if(!$response->successful(), LogicException::class, 'Search CCRIS failed.');

        $responseObj = simplexml_load_string($response->body());
        $refNo = $this->generateRefNo();

        $ccrisEntity = $this->confirmCcrisEntity(
            refNo: $refNo,
            refId: data_get($responseObj, 'ccris_identity.item.CRefId'),
            entityKey: data_get($responseObj, 'ccris_identity.item.EntityKey'),
            spgaIdentity: data_get($responseObj, 'ccris_identity.spga_identity') ?? null,
            phone: $phone,
            email: $email,
            address: $address
        );

        if ($ccrisEntity) {
            $experianRecord = ExperianRecord::create([
                'ref_no' => $refNo,
                'ccris_search' => $responseObj,
                'ccris_entity' =>  $ccrisEntity
            ]);

            $report = $this->retrieveReport(data_get($ccrisEntity, 'token1'), data_get($ccrisEntity, 'token2'));

            if ($experianRecord && $report) {
                $experianRecord->ccris_report = $report;
                $experianRecord->save();

                return [
                    'ref_no' => $experianRecord->ref_no,
                    'report' => $experianRecord->ccris_report,
                ];
            }
        }
    }

    public function confirmCcrisEntity(
        string $refNo,
        string $refId,
        string $entityKey,
        ?string $phone = null,
        ?string $email = null,
        ?string $address = null,
        array|object|null $spgaIdentity = null,
    ) {

        throw_if(!($phone || $email || $address), LogicException::class, 'Must supply at least one of this contact information: phone, email, address.');

        $data = [
            'CRefId' => $refId,
            'EntityKey' =>  $entityKey,
            'MobileNo' => $phone,
            'EmailAddress' => $email,
            'LastKnownAddress' => $address,
            'ConsentGranted' => 'Y',
            'EnquiryPurpose' => 'REVIEW',
            'Ref1' => $refNo,
        ];

        if ($spgaIdentity) {
            $data['ProductType'] = 'SPKCCS';
            $data['SRefId'] = data_get($spgaIdentity, 'SRefId');
        } else {
            $data['ProductType'] = 'IRISS';
        }

        $response = $this->method('post')
            ->action('confirmCcrisEntity')
            ->makeRequest('report', $data);

        throw_if(!$response->successful(), LogicException::class, 'Confirm CCRIS Entity failed.');

        return simplexml_load_string($response->body());
    }

    public function retrieveReport(
        string $token1,
        string $token2
    ) {

        $data = [
            'token1' => $token1,
            'token2' => $token2
        ];

        $response = $this->method('post')
            ->action('retrieveReport')
            ->makeRequest('report', $data);

        throw_if(!$response->successful(), LogicException::class, 'Retrieve record failed.');

        return simplexml_load_string($response->body());
    }

    private function makeRequest(string $endpoint, array $data = []): Response
    {
        throw_if(!$this->getAction(), LogicException::class, 'Action not set.');

        $experian = ExperianRequest::create([
            'action' => $this->getAction(),
            'request' => $data,
        ]);

        // convert data to xml
        $xmlBody = $this->array2xml($data);

        $response =  Http::withBasicAuth($this->getUsername(), $this->getPassword())
            ->withBody($xmlBody, 'application/xml')
            ->{$this->getMethod()}($this->getBaseUrl() . '/' . $endpoint);

        $response->throw(function (Response $response, RequestException $e) use ($experian) {
            $experian->error_response = $e->getMessage();
            $experian->save();
        });

        if ($response->successful()) {
            $response = simplexml_load_string($response->body());

            if (data_get($response, 'code') && data_get($response, 'code') != '200') {
                $experian->error_response = $response;
            } else {
                $experian->response = $response;
            }

            $experian->save();
        }

        return $response;
    }

    private function method(string $method)
    {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    private function action(string $action)
    {
        $this->action = $action;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setVendor(string $vendor): void
    {
        $this->vendor = $vendor;
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setSandboxMode(bool $sandboxMode): void
    {
        $this->sandboxMode = $sandboxMode;
    }

    public function getSandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    public function setBaseUrl(): void
    {
        $vendor = config('experian.vendor');

        if ($this->getSandboxMode() === true) {
            $this->baseUrl = config('experian.sandbox.base_url') . '/' . $vendor;
        } else {
            $this->baseUrl = config('experian.base_url') . '/' . $vendor;;
        }
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    private function array2xml($array, $xml = false)
    {
        // Test if iteration
        if ($xml === false) {
            $xml = new \SimpleXMLElement('<xml />');
            $request = $xml->addChild('request');
        }

        // Loop through array
        foreach ($array as $key => $value) {
            // Another array? Iterate
            if (is_array($value)) {
                self::array2xml($value, $xml->addChild($key));
            } else {
                $request->addChild($key, $value);
            }
        }

        // Return XML
        return $xml->asXML();
    }

    private function generateRefNo()
    {
        $ref_no = $this->randomAlphanumeric();

        while (ExperianRecord::where('ref_no', $ref_no)->count()) {
            $ref_no = $this->randomAlphanumeric();
        }

        return $ref_no;
    }

    private function randomAlphanumeric(int $length = 8)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        return substr(str_shuffle($characters), 0, $length);
    }
}
