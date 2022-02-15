<?php

namespace App\Model;

use JMS\Serializer\Annotation\Type;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class User
{
    /**
     * @Type(value="array<App\Entity\User>")
     */
    public $data;
    public $_links;
    public $meta;

    public function __construct(Pagerfanta $pagerFanta, UrlGeneratorInterface $router)
    {
        $this->data = $pagerFanta->getCurrentPageResults();

        $this->addMeta('limit', $pagerFanta->getMaxPerPage());
        $this->addMeta('current_items', count($pagerFanta->getCurrentPageResults()));
        $this->addMeta('total_items', $pagerFanta->getNbResults());
        $this->addMeta('total_pages', $pagerFanta->getNbPages());

        // Add Hypermedia
        if($pagerFanta->hasPreviousPage()) {
            $this->_links['previous_page']['href'] = $router->generate('user_list', [
                'page' => $pagerFanta->getPreviousPage()
            ],
            UrlGeneratorInterface::ABSOLUTE_URL);

        }

        $this->_links['current_page']['href'] = $router->generate('user_list', [
            'page' => $pagerFanta->getCurrentPage()
        ],
        UrlGeneratorInterface::ABSOLUTE_URL);
        
        if($pagerFanta->hasNextPage()) {
            $this->_links['next_page']['href'] =  $router->generate('user_list', [
                'page' => $pagerFanta->getNextPage()
            ],
        UrlGeneratorInterface::ABSOLUTE_URL);
        }
    }

    public function addMeta($name, $value)
    {
        if (isset($this->meta[$name])) {
            throw new \LogicException(sprintf('This meta already exists. You are trying to override this meta, use the setMeta method instead for the %s meta.', $name));
        }
        
        $this->setMeta($name, $value);
    }
    
    public function setMeta($name, $value)
    {
        $this->meta[$name] = $value;
    }
}