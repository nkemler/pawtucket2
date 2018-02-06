<?php
/* ----------------------------------------------------------------------
 * views/Browse/browse_refine_subview_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2014-2015 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
 
	$va_facets 			= $this->getVar('facets');				// array of available browse facets
	$va_criteria 		= $this->getVar('criteria');			// array of browse criteria
	$vs_key 			= $this->getVar('key');					// cache key for current browse
	$va_access_values 	= $this->getVar('access_values');		// list of access values for this user
	$vs_view			= $this->getVar('view');
	$vs_browse_type		= $this->getVar('browse_type');
	$o_browse			= $this->getVar('browse');
	
	$vn_facet_display_length_initial = 7;
	$vn_facet_display_length_maximum = 60;
	
	if(is_array($va_facets) && sizeof($va_facets)){
		foreach($va_facets as $vs_facet_name => $va_facet_info) {
			
			if (!is_array($va_facet_info['content']) || !sizeof($va_facet_info['content'])) { continue; }
			print "<div class='bRefineFacet'>";
			if ((caGetOption('deferred_load', $va_facet_info, false) || ($va_facet_info["group_mode"] == 'hierarchical')) && ($o_browse->getFacet($vs_facet_name))) {
				print "<H5><span>+</span>".$va_facet_info['label_singular']."</H5>";
?>
					<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery("#bHierarchyList_<?php print $vs_facet_name; ?>").load("<?php print caNavUrl($this->request, '*', '*', 'getFacetHierarchyLevel', array('facet' => $vs_facet_name, 'browseType' => $vs_browse_type, 'key' => $vs_key, 'linkTo' => 'morePanel')); ?>");
						});
					</script>
					<div id='bHierarchyList_<?php print $vs_facet_name; ?>'><?php print caBusyIndicatorIcon($this->request).' '.addslashes(_t('Loading...')); ?></div>
<?php
			} else {				
				print "<H5><span>+</span>".$va_facet_info['label_singular']."</H5>"; 
				switch($va_facet_info["group_mode"]){
					case "alphabetical":
					case "list":
					default:
						$vn_facet_size = sizeof($va_facet_info['content']);
						$vn_c = 0;
						foreach($va_facet_info['content'] as $va_item) {
						    $vs_content_count = (isset($va_item['content_count']) && ($va_item['content_count'] > 0)) ? $va_item['content_count'] : "";
							$vs_label = "";
							if($va_facet_info["table"] == "ca_entities"){
								$vs_label = $va_item["surname"].(($va_item["forename"]) ? ", ".$va_item["forename"] : "");
								if(!$vs_label){
									$vs_label = $va_item['label'];
								}
							}else{
								$vs_label = $va_item['label'];
							}
							print "<div>".caNavLink($this->request, "<span>".$vs_content_count."</span>".$vs_label, '', '*', '*','*', array('key' => $vs_key, 'facet' => $vs_facet_name, 'id' => $va_item['id'], 'view' => $vs_view))."</div>";
							$vn_c++;
						
// 							if (($vn_c == $vn_facet_display_length_initial) && ($vn_facet_size > $vn_facet_display_length_initial) && ($vn_facet_size <= $vn_facet_display_length_maximum)) {
// 								print "<div id='{$vs_facet_name}_more' style='display: none;'>";
// 							} else {
// 								if(($vn_c == $vn_facet_display_length_initial) && ($vn_facet_size > $vn_facet_display_length_maximum))  {
// 									break;
// 								}
// 							}
						}
// 						if (($vn_facet_size > $vn_facet_display_length_initial) && ($vn_facet_size <= $vn_facet_display_length_maximum)) {
// 							print "</div>\n";
// 						
// 							$vs_link_open_text = _t("and %1 more", $vn_facet_size - $vn_facet_display_length_initial);
// 							$vs_link_close_text = _t("close", $vn_facet_size - $vn_facet_display_length_initial);
// 							print "<div><a href='#' class='more' id='{$vs_facet_name}_more_link' onclick='jQuery(\"#{$vs_facet_name}_more\").slideToggle(250, function() { jQuery(this).is(\":visible\") ? jQuery(\"#{$vs_facet_name}_more_link\").text(\"".addslashes($vs_link_close_text)."\") : jQuery(\"#{$vs_facet_name}_more_link\").text(\"".addslashes($vs_link_open_text)."\")}); return false;'><em>{$vs_link_open_text}</em></a></div>";
// 						} elseif (($vn_facet_size > $vn_facet_display_length_initial) && ($vn_facet_size > $vn_facet_display_length_maximum)) {
// 							print "<div><a href='#' class='more' onclick='jQuery(\"#bMorePanel\").load(\"".caNavUrl($this->request, '*', '*', '*', array('getFacet' => 1, 'facet' => $vs_facet_name, 'view' => $vs_view, 'key' => $vs_key))."\", function(){jQuery(\"#bMorePanel\").show(); jQuery(\"#bMorePanel\").mouseleave(function(){jQuery(\"#bMorePanel\").hide();});}); return false;'><em>"._t("and %1 more", $vn_facet_size - $vn_facet_display_length_initial)."</em></a></div>";
// 						}
					break;
					# ---------------------------------------------
				}
			}
			print "</div><!-- end bRefineFacet -->";
		}	
	}
?>