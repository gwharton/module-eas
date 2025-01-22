<?php
namespace Gw\EAS\SDK\Requests;

use Exception;
use JsonMapper;
use Gw\EAS\SDK\Dto\CreatePostSaleOrderRequest as CreatePostSaleOrderRequestDto;
use Gw\EAS\SDK\Dto\CreatePostSaleOrderResponse;
use Gw\EAS\SDK\Dto\EmptyResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreatePostSaleOrderRequest extends BaseRequest implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param \Gw\EAS\SDK\Dto\CreatePostSaleOrderRequest $createPostSaleOrderRequestDto
     */
    public function __construct(
        public CreatePostSaleOrderRequestDto $createPostSaleOrderRequestDto
    ) {}

    public function resolveEndpoint(): string
    {
        return "/createpostsaleorder";
    }

    public function defaultBody(): array
    {
        return $this->toArray($this->createPostSaleOrderRequestDto);
    }

    public function createDtoFromResponse(Response $response): CreatePostSaleOrderResponse|EmptyResponse
    {
        $status = $response->status();
        $responseClass = match ($status) {
            200 => CreatePostSaleOrderResponse::class,
            206, 400, 401, 403, 404, 422, 500 => EmptyResponse::class,
            default => throw new Exception("Unhandled response status: {$status}")
        };
        if ($responseClass === CreatePostSaleOrderResponse::class) {
            list(, $base64UrlPayload, ) = explode('.', $response->body());
            $payload = $this->base64UrlDecode($base64UrlPayload);
            return (new JsonMapper)->map(json_decode($payload), $responseClass);
        }
        return (new JsonMapper)->map(json_decode($response->body()), $responseClass);
    }

    private function base64UrlDecode($data)
    {
        $base64 = strtr($data, '-_', '+/');
        $base64Padded = str_pad($base64, strlen($base64) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($base64Padded);
    }
}
