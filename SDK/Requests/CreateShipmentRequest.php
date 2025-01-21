<?php
namespace Gw\EAS\SDK\Requests;

use Exception;
use JsonMapper;
use Gw\EAS\SDK\Dto\CreateShipmentRequestDto;
use Gw\EAS\SDK\Responses\EmptyResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateShipmentRequest extends BaseRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param \Gw\EAS\SDK\Dto\CreateShipmentRequestDto $createShipmentRequestDto
     */
    public function __construct(
        public CreateShipmentRequestDto $createShipmentRequestDto
    ) {}

    public function resolveEndpoint(): string
    {
        return "/shipment/create_shipment";
    }

    public function defaultBody(): array
    {
        return $this->toArray($this->createShipmentRequestDto);
    }

    public function createDtoFromResponse(Response $response): EmptyResponse
    {
        $status = $response->status();
        $responseClass = match ($status) {
            200, 400, 401, 403, 404, 500 => EmptyResponse::class,
            default => throw new Exception("Unhandled response status: {$status}")
        };
        return (new JsonMapper)->map(json_decode($response->body()), $responseClass);
    }
}
