<?php
namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class FormattedDescription {
 private const TAGS=['p','div','span','br','h2','h3','h4','strong','b','em','i','u','s','del','ul','ol','li','blockquote','pre','code','hr','a','table','thead','tbody','tr','th','td'];
 private const DROP=['script','style','iframe','object','embed','svg','math','form','input','button','textarea','select','option','template','link','meta','base'];
 public static function sanitize(string $value): string {
  if(!preg_match('/<\/?[a-z][^>]*>/i',$value))return '<p>'.nl2br(htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'),false).'</p>';
  $document=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
  try {
   $document->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$value.'</body></html>',LIBXML_NONET);
   $body=$document->getElementsByTagName('body')->item(0);return $body?self::children($body):'';
  } finally {libxml_clear_errors();libxml_use_internal_errors($previous);}
 }
 private static function children(DOMNode $parent): string {
  $html='';foreach($parent->childNodes as $node){
   if($node->nodeType===XML_TEXT_NODE){$html.=htmlspecialchars($node->nodeValue,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');continue;}
   if(!$node instanceof DOMElement)continue;
   $tag=strtolower($node->tagName);if(in_array($tag,self::DROP,true))continue;
   $children=self::children($node);
   if(!in_array($tag,self::TAGS,true)){$html.=$children;continue;}
   $attributes='';
   if($tag==='a'){
    $href=trim($node->getAttribute('href'));
    if(preg_match('~^(?:https?://|mailto:|tel:|/(?!/)|#)~i',$href)&&!preg_match('/[\x00-\x20\x7f]/',$href)){
     $attributes=' href="'.htmlspecialchars($href,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'"';
     if($node->getAttribute('target')==='_blank')$attributes.=' target="_blank" rel="noopener noreferrer"';
    }
   }
   $html.='<'.$tag.$attributes.'>';if(!in_array($tag,['br','hr'],true))$html.=$children.'</'.$tag.'>';
  }return $html;
 }
 public static function text(string $value): string {
  $spaced=preg_replace('~</?(?:p|div|h[1-6]|li|ul|ol|blockquote|tr|td|th|table|br|hr|a)\b[^>]*>~i',' ',$value);
  $text=html_entity_decode(strip_tags($spaced),ENT_QUOTES|ENT_HTML5,'UTF-8');
  return trim(preg_replace('/[\s\x{00a0}\x{200b}]+/u',' ',$text));
 }
}
