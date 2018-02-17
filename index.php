<?php
/*
 * @Author: Manjeet Kumar Patel <manjeetkumar15@gmail.com> Mob : +91 9990050330  Expert in Website parser
 * @Description: This script parse data from url : https://supermarket.londis.co.uk 
    * By using simple_html_dom dome library 
 * @Created: 6 Feb 2018
 *@Modified: 6 Feb 2018
 
 */

include('simple_html_dom.php');
$html = file_get_html('https://supermarket.londis.co.uk/gb');  // get html content from website


$data= array(); //store all data in array format

for($data_counter=0; $data_counter<20; $data_counter++ )
{
	$result= @$html->find('.components-outlet-item-search-result-basic__link--clickable', $data_counter)->innertext;
	$plainhtml=   str_get_html($result);
	
	// Start get URL of another linked page
	$counter=0;	
	foreach($plainhtml->find('a') as $element)
	{
		$counter++;
		if($counter==2)
		{
			$data[$data_counter]['origin']="https://supermarket.londis.co.uk/".$element->href;	
		}
	}
	// End get URL of another linked page
	
	
	
	$new_page  = file_get_html($data[$data_counter]['origin']); 
	
	$data[$data_counter]['opening_hours']=array();
	$data[$data_counter]['latitude']='';
	$data[$data_counter]['longitude']='';
	
	if($new_page )
	{
		// Start Opening Hour Calculation 
		$open_result= @$new_page->find('.components-outlet-item-hours-retail__row', 0)->innertext ;
		if($open_result!='')
		{
			$openhtml = str_get_html(  $open_result );
			
			foreach($openhtml->find('.components-outlet-item-hours-retail__line') as $opn_time)
			{
				$open_time_data= $opn_time->innertext;
				$open_inner = str_get_html($open_time_data);
				$day = trim(@$open_inner->find('.components-outlet-item-hours-retail__line__day', 0)->innertext);
				$time = trim(@$open_inner->find('.components-outlet-item-hours-retail__line__time__value', 0)->innertext);
				
				$data[$data_counter]['opening_hours'][$day]=$time;
			}
		}
		// End Opening Hour Calculation 
		
		// Start  latitude ,longitude  Calculation 
		$latitude = @$new_page->find('meta[itemprop=latitude]');
		if(!empty($latitude))
		{
			foreach($latitude as $lat)
			{
				$data[$data_counter]['latitude']=  $lat->content;
			}
		}
		
		$longitude = @$new_page->find('meta[itemprop=longitude]');
		if(!empty($longitude))
		{
			foreach($longitude as $lng)
			{
				$data[$data_counter]['longitude']=  $lng->content;
			}
		}
		// End  latitude ,longitude  Calculation 
		
	}
	
	$data[$data_counter]['name'] =  trim(@$plainhtml->find('.components-outlet-item-search-result-basic__link__details__link__name__span', 0)->innertext);
	$data[$data_counter]['phone'] =  trim(@$plainhtml->find('.components-outlet-item-phone-basic__phone__number', 0)->innertext);
	$data[$data_counter]['street_address'] =  strip_tags(trim(@$plainhtml->find('.components-outlet-item-address-basic__line', 0)->innertext));
	//$data[$data_counter]['address_locality'] =  trim(@$plainhtml->find('span[itemprop="addressLocality"]', 0)->innertext);
	$data[$data_counter]['city'] =  trim(@$plainhtml->find('span[itemprop="addressRegion"]', 0)->innertext);
	$data[$data_counter]['zipcode'] =  trim(@$plainhtml->find('span[itemprop="postalCode"]', 0)->innertext);
}


echo  json_encode($data);