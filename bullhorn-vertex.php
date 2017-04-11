<?php
/**
 * Plugin Name: Vertex Bullhorn 
 * Plugin URI: http://www.vertex.com/
 * Description: This plugin pulls jobs from Bullhorn for front-end display and allows candidates to submit their resumes.
 * Version: 2.1.0
 * Author: Vertex
 * License: Vertex
 */

include_once('bullhorn-connect.php');
define('COUNT', 10);


add_shortcode('bullhorn','bullhorn_shortcode');
function bullhorn_shortcode($atts, $content = null){
//	wp_enqueue_script('bullhorn', plugin_dir_url( __FILE__ ) . '/js/jquery.js', array('jquery'), false, true);
	wp_enqueue_script('bullhorn', plugin_dir_url( __FILE__ ) . '/js/bullhorn.js', array('jquery'), false, true);
	wp_enqueue_style('bullhorn', plugin_dir_url( __FILE__ ) . '/css/bullhorn.css', array());
	try {
		$bullhornConnect = new BullhornConnect();
		$tokens= $bullhornConnect->bullhorn_login();
//		var_dump($tokens);		
		$bullhornStart = 0;
		if (isset($_GET['nav'])){
			if ($_GET['nav'] == 'next'){
				$bullhornStart = $_GET['current'] + COUNT;
			}else{
				$bullhornStart = $_GET['current'] - COUNT;
				$bullhornStart = ($bullhornStart >= 0 ? $bullhornStart : 0);
			}
		}		
		$searchWord = (isset($_GET['search']) && !empty($_GET['search']) ? 'AND title: ('.$_GET['search'].'*)' : ' ');
		
		$jobs = $bullhornConnect->httpGETRequest($tokens['restUrl']."/search/JobOrder?BhRestToken=".$tokens['BhRestToken'].$bullhornConnect->urlBullhornEncode('&query=isOpen:1 AND isDeleted:0 AND status:("accepting candidates") '.$searchWord.'&fields=id,clientCorporation,clientContact,description,status,title,dateAdded,address&count='.COUNT.'&start='.$bullhornStart));												
		
		$ret = '<div>Job Search: <input type="text" id="bullhornSearch" value="'.(isset($_GET['search']) ? $_GET['search'] : '').'"> <a href="javascript:" class="bullhorn-button" onclick="var search = document.getElementById(\'bullhornSearch\').value; window.location.href=\''.get_permalink().'?search=\'+search" class="btn-search">Search</a></div>';
		foreach($jobs['data'] as $job){
			$ret .= '<div class="bullhorn-wrapper"><h3>'.$job['title'].'</h3>'.
						'<div><span>Posted on: '.date('m/d/y',$job['dateAdded']/1000).'</span>. <span>'.$job['address']['city'].', '.$job['address']['state'].'</span><span class="status">'.$job['status'].'</span></div>'.
						'<a href="javascript:" class="viewDetails">View Details</a>'.
						'<div class="bullhorn-description" style="display:none">'.$job['description'].
						'<a class="bullhorn-button"  href="'.get_site_url().'/apply-for-job/?bullhornJob='.$job['id'].'&bullhornJobTitle='.$job['title'].'">Apply This Job</a></div>'.
					'</div>';
			}
		$ret .= '<p><span class="float-right">Found: '.$jobs['total'].' jobs</span></p><div style="clear:both">';
		if ($bullhornStart > 0 )$ret .= '<a class="bullhorn-button" href="'.get_permalink().'?search='.$searchWord.'&nav=prev&current='.$bullhornStart.'" class="btn">Previous</a>';
		if ($bullhornStart < $jobs['total']-COUNT)$ret .= '<a class="bullhorn-button float-right" href="'.get_permalink().'?search='.$searchWord.'&nav=next&current='.$bullhornStart.'">Next</a>';
		return $ret.'</div>';
		die;
	} catch (Exception $e) {
		error_log($e->getMessage());
	}

}

