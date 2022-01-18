<?php

namespace CEhlers\Shortcode;

class TextFragment
{
    private array $metaInfos;
    private string $id;
    protected string $rawText;
    protected string $syncKey;

    public function __construct(string $rawText){
        $this->metaInfos = [];
        $this->rawText = $rawText;
        $this->id = ObjectInstanceUtils::generateInstanceId();
        $this->syncKey = 'sk-'.$this->id;
        $this->evalRaw($rawText);
        $this->addMetaInfo('instance-ID',$this->id);
    }

    public function evalRaw(string $rawText):TextFragment{
        return $this;
    }

    public function __toString(){return $this->rawText;}
    public function toJson():string {
        return json_encode([
            'content' => $this->rawText,
            'fragmentType' => 'text',
            'id' => $this->id,
            'metaInfos' => $this->getMetaInfos(),
            'syncKey' => $this->syncKey
        ]);
    }
    public function toHtmlList(){
        return "<li class='".$this->getSyncKey()." fragment fragment--text'>".$this."</li>";
    }
    public function getInnerFragments(){return [];}

    public function getId():string{
        return $this->id;
    }

    public function getRaw():string {return $this->rawText;}

    final public function getSyncKey():string{
        return $this->syncKey;
    }
    public function setSyncKey(string $syncKey):TextFragment{
        $this->syncKey = $syncKey;
        return $this;
    }

    final public function addMetaInfo(string $metaKey, string $metaValue):TextFragment {
        $this->metaInfos[$metaKey] = $metaValue;
        return $this;
    }
    final public function getMetaInfo(string $metaKey,string $defaultValue=""):string {
        if(array_key_exists($metaKey,$this->metaInfos)){
            return $this->metaInfos[$metaKey];
        }
        return $defaultValue;
    }
    final public function getMetaInfos():array {
        return $this->metaInfos;
    }
}
