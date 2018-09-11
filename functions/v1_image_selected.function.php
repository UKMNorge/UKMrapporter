<?php
function image_selected( $innslag, $tittel = false, $type = 'bilde', $size='thumbnail' ) {
	$valgtBilde = new SQL("SELECT *
						   FROM `smartukm_videresending_media` AS `media`
						   JOIN `ukmno_wp_related` ON (`ukmno_wp_related`.`rel_id` = `media`.`rel_id`)
						   WHERE `media`.`b_id` = '#bid'
						   AND `m_type` = '#type'
  						   AND (`t_id` = '0' OR `t_id` = '#tid' OR `t_id` IS NULL)",
						  array('bid'=>$innslag->ID,'tid'=>$tittel, 'type' => $type)
						 );
	$bilde = $valgtBilde->run('field','rel_id');
	
	if( $bilde == null )
		return 'none_selected';
	
	// CALC IMAGE DATA
	$bilde = $valgtBilde->run('array');
	
	$post_meta = unserialize( $bilde['post_meta'] );
	if( $size == 'thumbnail' && isset( $post_meta['sizes']['thumbnail']['file'] ) ) {
		$src = $post_meta['sizes']['thumbnail']['file'];
	} else {
		$src = $post_meta['file'];
	}
	
	$src = 'https://'. $_SERVER['HTTP_HOST'].'/wp-content/blogs.dir/'. $bilde['blog_id'].'/files/' . $src;
	
	$vb = new stdClass();
	$vb->ID = $bilde['rel_id'];
	$vb->src = $src;
	return $vb;
}