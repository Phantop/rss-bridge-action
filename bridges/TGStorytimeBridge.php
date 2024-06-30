<?php

class TGStorytimeBridge extends BridgeAbstract
{
    const NAME = 'TG Storytime';
    const URI = 'https://tgstorytime.com/';
    const CACHE_TIMEOUT = 1800;
    const DESCRIPTION = 'Returns chapters from TG Storytime';
    const MAINTAINER = 'phantop';
    const PARAMETERS = [
        'Story' => [
            'id' => [
                'name' => 'id',
                'required' => true,
                // Example: latest chapters from Moonlit Waters by September
                'exampleValue' => '6989',
            ],
        ]
    ];

    public function collectData()
    {
        $id = $this->getInput('id');
        $url = self::URI . "viewstory.php?sid=$id&index=1&ageconsent";
        $html = getSimpleHTMLDOM($url);

        $this->title  = $html->find('#pagetitle a', 0)->plaintext;

        foreach ($html->find('#chapterlist') as $element) {
            $item = [];

            $item['title'] = $element->find('a', 0)->plaintext;
            $item['author'] = $element->find('a', 1)->plaintext;
            $item['uri'] = self::URI . str_replace('&amp;', '&', $element->find('a', 0)->href) . '&ageconsent';
            $item['content'] = $element->plaintext;


            if ($item_html = getSimpleHTMLDOMCached($item['uri'])) {
                $item['content'] = $item_html->find('#story > span', 0)->innertext;
            }

            $item['uid'] = $item['uri'];

            $this->items[] = $item;
        }

        $this->items = array_reverse($this->items);
    }

    public function getName()
    {
        $name = parent::getName() . " $this->queriedContext";
        if (isset($this->title)) {
            $name .= " - $this->title";
        }
        return $name;
    }

    public function getIcon()
    {
        return self::URI . '/favicon.ico';
    }
}
