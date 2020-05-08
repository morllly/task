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
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * MainController constructor.
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(Request $request): Response
    {
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('X-Request-Time', \date_create()->format(\DateTime::ATOM));

        $serializerContext = [JsonEncode::OPTIONS => JSON_PRETTY_PRINT];

        $response->setContent($this->twig->render('index.html.twig', [
            'env_vars' => $this->serializer->serialize($this->makeEnvVars(), 'json', $serializerContext),
            'server' => $this->serializer->serialize($request->server, 'json', $serializerContext),
            'post' => $this->serializer->serialize($request->request, 'json', $serializerContext),
            'get' => $this->serializer->serialize($request->query, 'json', $serializerContext),
            'headers' => $this->serializer->serialize($request->headers, 'json', $serializerContext),
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
