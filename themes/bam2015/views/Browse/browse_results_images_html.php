<?php
/* ----------------------------------------------------------------------
 * views/Browse/browse_results_images_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2014 Whirl-i-Gig
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
 
	$qr_res 			= $this->getVar('result');				// browse results (subclass of SearchResult)
	$va_facets 			= $this->getVar('facets');				// array of available browse facets
	$va_criteria 		= $this->getVar('criteria');			// array of browse criteria
	$vs_browse_key 		= $this->getVar('key');					// cache key for current browse
	$va_access_values 	= $this->getVar('access_values');		// list of access values for this user
	$vn_hits_per_block 	= (int)$this->getVar('hits_per_block');	// number of hits to display per block
	$vn_start		 	= (int)$this->getVar('start');			// offset to seek to before outputting results
	
	$va_views			= $this->getVar('views');
	$vs_current_view	= $this->getVar('view');
	$va_view_icons		= $this->getVar('viewIcons');
	$vs_current_sort	= $this->getVar('sort');
	
	$t_instance			= $this->getVar('t_instance');
	$vs_table 			= $this->getVar('table');
	$vs_pk				= $this->getVar('primaryKey');
	$va_access_values = caGetUserAccessValues($this->request);
	$o_config = $this->getVar("config");	
	
	$va_options			= $this->getVar('options');
	$vs_extended_info_template = caGetOption('extendedInformationTemplate', $va_options, null);

	$vb_ajax			= (bool)$this->request->isAjax();
	

	$va_add_to_set_link_info = caGetAddToSetInfo($this->request);

	$o_icons_conf = caGetIconsConfig();
	$va_object_type_specific_icons = $o_icons_conf->getAssoc("placeholders");
	if(!($vs_default_placeholder = $o_icons_conf->get("placeholder_media_icon"))){
		$vs_default_placeholder = "<i class='fa fa-picture-o fa-2x'></i>";
	}
	$vs_default_placeholder_tag = "<div class='bResultItemImgPlaceholder'>".$vs_default_placeholder."</div>";
		

		if ($vn_start < $qr_res->numHits()) {
			$vn_c = 0;
			$qr_res->seek($vn_start);
			
			if ($vs_table != 'ca_objects') {
				$va_ids = array();
				while($qr_res->nextHit() && ($vn_c < $vn_hits_per_block)) {
					$va_ids[] = $qr_res->get($vs_pk);
					$vn_c++;
				}
				$va_images = caGetDisplayImagesForAuthorityItems($vs_table, $va_ids, array('version' => 'small', 'relationshipTypes' => caGetOption('selectMediaUsingRelationshipTypes', $va_options, null), 'checkAccess' => $va_access_values));
				$va_images_2 = array();
				# --- default to any related image if the configured relationship type is not available
				if(caGetOption('selectMediaUsingRelationshipTypes', $va_options, null)){
					$va_images_2 = caGetDisplayImagesForAuthorityItems($vs_table, $va_ids, array('version' => 'small', null, 'checkAccess' => $va_access_values));	
				}
				$vn_c = 0;	
				$qr_res->seek($vn_start);
			}
			
			$t_list_item = new ca_list_items();
			$vs_add_to_lightbox_msg = addslashes(_t('Add to %1', $vs_lightbox_display_name));
			while($qr_res->nextHit() && ($vn_c < $vn_hits_per_block)) {
				$vn_id 					= $qr_res->get("{$vs_table}.{$vs_pk}");
				$vs_idno_detail_link 	= caDetailLink($this->request, $qr_res->get("{$vs_table}.idno"), '', $vs_table, $vn_id);
				$vs_label_detail_link 	= caDetailLink($this->request, $qr_res->get("{$vs_table}.preferred_labels.name"), '', $vs_table, $vn_id);
				if($vs_table == 'ca_occurrences'){
					$vn_chop_len = 90;
					$vs_date_conjunction = "<br/>";
					if($vs_current_view == "list"){
						$vn_chop_len = 40;
						$vs_date_conjunction = ", ";
					}
					$vs_link_text = ($qr_res->get("{$vs_table}.preferred_labels")) ? $qr_res->get("{$vs_table}.preferred_labels") : $qr_res->get("{$vs_table}.idno");
					if(mb_strlen($vs_link_text) > $vn_chop_len){
						$vs_link_text = mb_substr($vs_link_text, 0, $vn_chop_len)."...";
					}						
					if($qr_res->get("ca_occurrences.productionDate")){
						$vs_link_text = $vs_link_text.$vs_date_conjunction.$qr_res->get("ca_occurrences.productionDate", array("delimiter" => ", "));
					}
				}else{
					$vs_link_text = ($qr_res->get("{$vs_table}.preferred_labels")) ? $qr_res->get("{$vs_table}.preferred_labels") : $qr_res->get("{$vs_table}.idno");
				}
				$vs_thumbnail = "";
				$vs_type_placeholder = "";
				$vs_typecode = "";
				if ($vs_table == 'ca_objects') {
					$t_list_item->load($qr_res->get("type_id"));
					$vs_typecode = $t_list_item->get("idno");
					$vs_type_placeholder = caGetPlaceholder($vs_typecode, "placeholder_media_icon");
					if(!($vs_thumbnail = $qr_res->getMediaTag('ca_object_representations.media', 'small', array("checkAccess" => $va_access_values)))){
						if($vs_type_placeholder){
							$vs_thumbnail = "<div class='bResultItemImgPlaceholder'>".$vs_type_placeholder."</div>";
						}else{
							$vs_thumbnail = $vs_default_placeholder_tag;
						}
					}
					
					if(!$this->request->getParameter("openResultsInOverlay", pInteger)){
						$vs_rep_detail_link 	= caDetailLink($this->request, $vs_thumbnail, '', $vs_table, $vn_id);
					}else{
						$vs_rep_detail_link = "<a href='#' onclick='caMediaPanel.showPanel(\"".caNavUrl($this->request, 'Detail', 'objects', $vn_id, array('overlay' => 1))."\"); return false;'>".$vs_thumbnail."</a>";
					}
					if(!$this->request->getParameter("openResultsInOverlay", pInteger)){
						$vs_caption_detail_link 	= caDetailLink($this->request, $vs_link_text, '', $vs_table, $vn_id);
					}else{
						$vs_caption_detail_link = "<a href='#' onclick='caMediaPanel.showPanel(\"".caNavUrl($this->request, 'Detail', 'objects', $vn_id, array('overlay' => 1))."\"); return false;'>".$vs_link_text."</a>";
					}
					$vs_add_to_set_link = "";
					if(is_array($va_add_to_set_link_info) && sizeof($va_add_to_set_link_info)){
						$vs_add_to_set_link = "<div class='bBAMResultLB'><a href='#' onclick='caMediaPanel.showPanel(\"".caNavUrl($this->request, '', $va_add_to_set_link_info["controller"], 'addItemForm', array($vs_pk => $vn_id))."\"); return false;' title='".$va_add_to_set_link_info["link_text"]."'>".$va_add_to_set_link_info["icon"]."</a></div>";
					}				
				} else {
					if($va_images[$vn_id] || $va_images_2[$vn_id]){
						if($va_images[$vn_id]){
							$vs_thumbnail = $va_images[$vn_id];
						}else{
							$vs_thumbnail = $va_images_2[$vn_id];
						}
					}else{
						$vs_thumbnail = $vs_default_placeholder_tag;
					}
					$vs_rep_detail_link 	= caDetailLink($this->request, $vs_thumbnail, '', $vs_table, $vn_id);	
					$vs_caption_detail_link 	= caDetailLink($this->request, $vs_link_text, '', $vs_table, $vn_id);		
				}
				
				print "
	<div class='col-xs-12 col-sm-".(($this->request->getParameter("openResultsInOverlay", pInteger) || $this->request->getParameter("homePage", pInteger)) ? "3" : "4")."'>
		<div class='bBAMResultItem'>
			<div class='bSetsSelectMultiple bSetsSelectMultipleCheckbox'><input type='checkbox' name='object_ids' value='{$vn_id}'></div>
			<div class='bBAMResultItemImgContainer' ><div class='bBAMResultItemImg' ><span style='position:relative;display:inline-block;'>{$vs_add_to_set_link}{$vs_rep_detail_link}</span></div></div>
			<div class='bBAMResultItemText'>
				<div class='bBAMIcon'>{$vs_type_placeholder}</div>
				".$vs_caption_detail_link."
			</div>
		</div><!-- end bBAMResultItem -->
	</div><!-- end col -->";
				
				$vn_c++;
			}
			
			print caNavLink($this->request, _t('Next %1', $vn_hits_per_block), 'jscroll-next', '*', '*', '*', array('s' => $vn_start + $vn_hits_per_block, 'key' => $vs_browse_key, 'view' => $vs_current_view, 'openResultsInOverlay' => $this->request->getParameter("openResultsInOverlay", pInteger)));
		}
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		if($("#bSetsSelectMultipleButton").is(":visible")){
			$(".bSetsSelectMultiple").show();
		}
	});
</script>