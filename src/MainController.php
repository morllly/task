<?php declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Serializer\{Encoder\JsonEncode, SerializerInterface};
use Twig\Environment;
use Twig\Error\{LoaderError, RuntimeError, SyntaxError};

/**
 * Class MainController
 */
class MainController
{
    private const QUOTE = <<<TXT
I have a dream that one day, down in Alabama, with its vicious racists, with its governor having his lips dripping with the words of "interposition" and "nullification" — one day right there in Alabama little black boys and black girls will be able to join hands with little white boys and white girls as sisters and brothers.
TXT;

    private SerializerInterface $serializer;
    private Environment $twig;

    /**
     * @param SerializerInterface $serializer
     * @param Environment $twig
     */
    public function __construct(SerializerInterface $serializer, Environment $twig)
    {
        $this->serializer = $serializer;
        $this->twig = $twig;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws LoaderError|RuntimeError|SyntaxError
     */
    public function __invoke(Request $request): Response
    {
        $response = new Response();
        $response->prepare($request);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('X-Request-Time', \date_create()->format(\DateTime::ATOM));

        $serializerContext = [JsonEncode::OPTIONS => JSON_PRETTY_PRINT];

        $response->setContent($this->twig->render('index.html.twig', [
            'env_vars' => $this->serializer->serialize($this->makeEnvVars(), 'json', $serializerContext),
            'server' => $this->serializer->serialize($request->server, 'json', $serializerContext),
            'post' => $this->serializer->serialize($request->request, 'json', $serializerContext),
            'get' => $this->serializer->serialize($request->query, 'json', $serializerContext),
            'headers' => $this->serializer->serialize($response->headers, 'json', $serializerContext),
            'quotation' => self::QUOTE,
        ]));

        return $response;
    }

    /**
     * @return array
     */
    private function makeEnvVars(): array
    {
        return \array_filter(\getenv(), fn(string $key) => \strpos($key, 'APP') === 0, ARRAY_FILTER_USE_KEY);
    }
}
