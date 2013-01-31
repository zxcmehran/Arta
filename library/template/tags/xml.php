<?php 
/**
 * XML Tags generation class is included here.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaTemplate
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}

/**
 * An class to output XML codes in an special way.
 */
class ArtaTagsXML{
	
	/**
	 * Returns RSS2 from XML file.
	 * @param	object	$data	Object to create RSS from it.
	 * @return	string	XML file.
	 */
	static function RSS2($data){
		// title description link
		
		$feed = "<rss version=\"2.0\">\n";
		$feed.= "	<channel>\n";
		$feed.= "		<title>".htmlspecialchars($data->title)."</title>\n";
		$feed.= "		<description>".htmlspecialchars(strip_tags($data->description))."</description>\n";
		$feed.= "		<link>".htmlspecialchars($data->link)."</link>\n";
		$feed.= "		<lastBuildDate>".htmlspecialchars(gmdate('r', time()))."</lastBuildDate>\n";
		$feed.= "		<generator>".ArtaVersion::getCredits(false)."</generator>\n";

		if ($data->image!=null)
		{
			$feed.= "		<image>\n";
			$feed.= "			<url>".htmlspecialchars($data->image->url)."</url>\n";
			$feed.= "			<title>".htmlspecialchars($data->image->title)."</title>\n";
			$feed.= "			<link>".htmlspecialchars($data->image->link)."</link>\n";
			if ($data->image->width != "") {
				$feed.= "			<width>".htmlspecialchars($data->image->width)."</width>\n";
			}
			if ($data->image->height!="") {
				$feed.= "			<height>".htmlspecialchars($data->image->height)."</height>\n";
			}
			if ($data->image->description!="") {
				$feed.= "			<description>".htmlspecialchars($data->image->description)."</description>\n";
			}
			$feed.= "		</image>\n";
		}
		if ($data->language!="") {
			$feed.= "		<language>".htmlspecialchars($data->language)."</language>\n";
		}
		if ($data->copyright!="") {
			$feed.= "		<copyright>".htmlspecialchars($data->copyright)."</copyright>\n";
		}
		if ($data->editor!="") {
			$feed.= "		<managingEditor>".htmlspecialchars($data->editor)."</managingEditor>\n";
		}
		if ($data->webmaster!="") {
			$feed.= "		<webMaster>".htmlspecialchars($data->webmaster)."</webMaster>\n";
		}
		if ($data->pubDate!="") {
			$feed.= "		<pubDate>".htmlspecialchars(ArtaDate::translate($data->pubDate, 'r'))."</pubDate>\n";
		}
		if ($data->category!="") {
			$feed.= "		<category>".htmlspecialchars($data->category)."</category>\n";
		}
		if ($data->docs!="") {
			$feed.= "		<docs>".htmlspecialchars($data->docs)."</docs>\n";
		}
		if ($data->ttl!="") {
			$feed.= "		<ttl>".htmlspecialchars($data->ttl)."</ttl>\n";
		}
		if ($data->rating!="") {
			$feed.= "		<rating>".htmlspecialchars($data->rating)."</rating>\n";
		}
		if ($data->skipHours!="") {
			$feed.= "		<skipHours>".htmlspecialchars($data->skipHours)."</skipHours>\n";
		}
		if ($data->skipDays!="") {
			$feed.= "		<skipDays>".htmlspecialchars($data->skipDays)."</skipDays>\n";
		}

		for ($i=0; $i<count($data->items); $i++)
		{
			$feed.= "		<item>\n";
			$feed.= "			<title>".htmlspecialchars($data->items[$i]->title)."</title>\n";
			$feed.= "			<link>".htmlspecialchars($data->items[$i]->link)."</link>\n";
			$feed.= "			<description>".htmlspecialchars($data->items[$i]->description)."</description>\n";

			if ($data->items[$i]->author!="") {
				$feed.= "			<author>".htmlspecialchars($data->items[$i]->author)."</author>\n";
			}
			if ($data->items[$i]->category!="") {
				$feed.= "			<category>".htmlspecialchars($data->items[$i]->category)."</category>\n";
			}
			if ($data->items[$i]->comments!="") {
				$feed.= "			<comments>".htmlspecialchars($data->items[$i]->comments)."</comments>\n";
			}
			if ($data->items[$i]->date!="") {
				$feed.= "			<pubDate>".htmlspecialchars(ArtaDate::translate($data->items[$i]->date, 'r'))."</pubDate>\n";
			}
			if ($data->items[$i]->guid!="") {
				$feed.= "			<guid>".htmlspecialchars($data->items[$i]->guid)."</guid>\n";
			}
			if ($data->items[$i]->enclosure != NULL)
			{
					$feed.= "			<enclosure url=\"";
					$feed.= htmlspecialchars($data->items[$i]->enclosure->url);
					$feed.= "\" length=\"";
					$feed.= htmlspecialchars($data->items[$i]->enclosure->length);
					$feed.= "\" type=\"";
					$feed.= htmlspecialchars($data->items[$i]->enclosure->type);
					$feed.= "\"/>\n";
			}

			$feed.= "		</item>\n";
		}
		$feed.= "	</channel>\n";
		$feed.= "</rss>\n";
		return $feed;
		
		$xml = <<<XML
<?xml version="1.0"?> 
<rss version="2.0">
	<channel>
XML;
		
		
		
		$xml .= <<<XML
	</channel>
</rss>
XML;
		
	}
		
}
?>