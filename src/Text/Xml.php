<?php namespace Laraext\Text;

class Xml {


    static function toArray($xmlstr)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xmlstr);
        $root = $doc->documentElement;
        $output = self :: nodeToArray($root);
        return $output;
    }

    private static function nodeToArray($node)
    {
        $output = array();
        switch ($node->nodeType)
        {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i=0, $m=$node->childNodes->length; $i<$m; $i++)
                {
                    $child = $node->childNodes->item($i);
                    $v = self :: nodeToArray($child);
                    if(isset($child->tagName))
                    {
                        $t = $child->tagName;
                        if(!isset($output[$t]))
                        {
                            $output[$t] = [];
                        }
                        $output[$t][] = $v;
                    }
                    elseif($v || $v === '0')
                    {
                        $output = (string) $v;
                    }
                }
                if($node->attributes->length && !is_array($output))
                {
                    $output = ['@content'=>$output];
                }
                if(is_array($output))
                {
                    if($node->attributes->length)
                    {
                        $a = array();
                        foreach($node->attributes as $attrName => $attrNode)
                        {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v)
                    {
                        if(is_array($v) && count($v)==1 && $t!='@attributes')
                        {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

}
