<?php
namespace Tapsilat\Tests;

use PHPUnit\Framework\TestCase;
use Tapsilat\TapsilatAPI;
use Tapsilat\Models\SubmerchantCreateDTO;
use Tapsilat\Models\SubmerchantUpdateDTO;

class SubmerchantTest extends TestCase
{
    public function testCreateSubmerchant()
    {
        $request = new SubmerchantCreateDTO(
            "Address", "City", "Country", "email@example.com", "5555555555",
            "TR123", "12345678901", "Name", "Contact", "Surname", "PERSONAL",
            "Tax Office", "34000"
        );
        $expectedResponse = ['id' => 'sub-123'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)->onlyMethods(['makeRequest'])->getMock();
        $apiMock->expects($this->once())->method('makeRequest')
            ->with('POST', '/submerchants', null, $request->toArray())
            ->willReturn($expectedResponse);

        $result = $apiMock->createSubmerchant($request);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetSubmerchant()
    {
        $id = "sub-123";
        $expectedResponse = ['id' => 'sub-123', 'name' => 'Name'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)->onlyMethods(['makeRequest'])->getMock();
        $apiMock->expects($this->once())->method('makeRequest')
            ->with('GET', "/submerchants/{$id}")
            ->willReturn($expectedResponse);

        $result = $apiMock->getSubmerchant($id);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetSuborganizationBySubmerchant()
    {
        $id = "sub-123";
        $expectedResponse = ['sub_organization' => 'org-123'];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)->onlyMethods(['makeRequest'])->getMock();
        $apiMock->expects($this->once())->method('makeRequest')
            ->with('GET', "/submerchants/{$id}/suborganization")
            ->willReturn($expectedResponse);

        $result = $apiMock->getSuborganizationBySubmerchant($id);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testUpdateSubmerchant()
    {
        $id = "sub-123";
        $request = new SubmerchantUpdateDTO("New Address");
        $expectedResponse = ['success' => true];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)->onlyMethods(['makeRequest'])->getMock();
        $apiMock->expects($this->once())->method('makeRequest')
            ->with('PATCH', "/submerchants/{$id}", null, $request->toArray())
            ->willReturn($expectedResponse);

        $result = $apiMock->updateSubmerchant($id, $request);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testDeleteSubmerchant()
    {
        $id = "sub-123";
        $expectedResponse = ['success' => true];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)->onlyMethods(['makeRequest'])->getMock();
        $apiMock->expects($this->once())->method('makeRequest')
            ->with('DELETE', "/submerchants/{$id}")
            ->willReturn($expectedResponse);

        $result = $apiMock->deleteSubmerchant($id);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testListSubmerchants()
    {
        $expectedResponse = ['data' => [], 'total' => 0];

        $apiMock = $this->getMockBuilder(TapsilatAPI::class)->onlyMethods(['makeRequest'])->getMock();
        $apiMock->expects($this->once())->method('makeRequest')
            ->with('GET', "/submerchants", ['page' => 1, 'per_page' => 10])
            ->willReturn($expectedResponse);

        $result = $apiMock->listSubmerchants(1, 10);
        $this->assertEquals($expectedResponse, $result);
    }
}
