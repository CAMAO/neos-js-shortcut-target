<?php

namespace VIVOMEDIA\JsShortcutTarget\Aspect;

use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class JsShortcutTargetAspect
{
    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;
    /**
     * @Flow\Around("method(Neos\Flow\Mvc\Routing\UriBuilder->uriFor())")
     * @param \Neos\Flow\Aop\JoinPointInterface $joinPoint
     * @return string
     */
    public function rewritePluginViewUris(JoinPointInterface $joinPoint)
    {
        $arguments = $joinPoint->getMethodArguments();

        $node = $arguments['controllerArguments']['node'] ?? null;
        $contentRepository = $this->contentRepositoryRegistry->get($node->contentRepositoryId);
        if ($node && $node instanceof Node && $contentRepository->getNodeTypeManager()->getNodeType($node->nodeTypeName)?->isOfType('Neos.Neos:Shortcut')) {
            $target = $node->getProperty('target');
            if (strpos($target, 'javascript:') !== false) {
                return $target;
            }
        }
        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }
}
