<?php if(!defined('ARTA_VALID')){die('No access');}
$user=$this->get('user');
$info=$this->get('info');

?>
<fieldset><legend><?php echo trans('ONLINE USER DETAILS') ?></legend>
<h3><?php echo htmlspecialchars($user->name.' ('.$user->username.')') ?></h3>
<table>
	<tr>
		<td class="label"><?php echo trans('ONLINE SINCE') ?></td><td class="value"><?php echo ArtaDate::_($user->lastvisit_date); ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo trans('LAST CLICK') ?></td><td class="value"><?php echo ArtaDate::_($info->time); ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo trans('CLIENT') ?></td><td class="value"><?php echo trans($info->client); ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo trans('PLATFORM') ?></td><td class="value"><?php echo ucfirst(ArtaBrowser::getPlatform($info->agent));
		if(ArtaBrowser::isMobile($info->agent)){
			echo ' ('.trans('MOBILE').')';
		} ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo trans('USERAGENT') ?></td><td dir="ltr" class="value"><?php echo htmlspecialchars($info->agent);?></td>
	</tr>
	<tr>
		<td class="label"><?php echo trans('POSITION') ?></td><td class="value"><?php 
		if(($p=explode(',', $info->position))==true){
			echo htmlspecialchars(implode(' / ', $p));
		}
		?></td>
	</tr>
	<tr>
		<td class="label"><?php echo trans('IP ADDRESS') ?></td><td class="value"><?php 
		echo htmlspecialchars($info->ip); 
		if(($p=@gethostbyaddr($info->ip)) && $p!=$info->ip){
			echo ' ('.htmlspecialchars($p).')';
		}
		?></td>
	</tr>
	<tr>
		<td class="label"><?php echo trans('GEO INFO') ?></td><td class="value"><?php
		$failure=trans('CANNOT FINDOUT');
		if(substr($info->ip,0,7)!='192.168' && substr($info->ip,0,7)!='169.254' && $info->ip!='127.0.0.1'){ 
			$db=ArtaLoader::DB();
			ArtaLoader::Import('misc->yql');
			$res = ArtaYQL::getResult('SELECT * FROM pidgets.geoip WHERE ip='.$db->Quote($info->ip));
			
			if($res==false OR is_object($res)==false){
				echo $failure;
			}elseif(@$res->query->results->Result->country_code!=null){
				$res=$res->query->results->Result;
				if($res->city==null){
					$res->city='Unknown City';
				}
				
				echo '<p align="center" dir="ltr">'.$res->city.', '.$res->country_name.' ('.$res->country_code.')</p>';
				
				if($res->latitude===null||$res->latitude===false){
					$img=$res->city.','.$res->country_code.'&zoom=11';
				}else{
					$img=$res->latitude.','.$res->longitude.'&zoom=11';
				}

				if($res->city=='Unknown City'){
					$img=$res->country_code.'&zoom=5';
				}
		
				echo '<br/><p align="center"><img width="400" height="400" src="http://maps.google.com/maps/api/staticmap?center='.$img.'&size=400x400&sensor=false&format=png" alt="Map"/></p>';
		
			}else{
				echo trans('NO INTERNET IP');
			}
			//put ip.location instead of pidgets.geoip
			/*if($res==false OR is_object($res)==false){
				echo $failure;
			}elseif($res->query->results->Response->CountryName!='Reserved'){
				$res=$res->query->results->Response;
				if($res->Status!='OK'){
					echo $failure;
				}else{
					if($res->City==null){
						$res->City='Unknown City';
					}
					if($res->RegionName==null){
						$res->RegionName='Unknown Region';
					}
					echo $res->City.', '.$res->RegionName.', '.$res->CountryName.' ('.$res->CountryCode.')';
					$img=$res->Latitude.','.$res->Longitude.'&zoom=11';
					//$img=$res->City.','.$res->RegionName.','.$res->CountryCode.'&zoom=11';
					if($res->City=='Unknown City'){
						$img=$res->RegionName.','.$res->CountryCode.'&zoom=8';
						if($res->RegionName=='Unknown Region'){
							$img=$res->CountryCode.'&zoom=5';
						}
					}
			
					echo '<p><img width="400" height="400" src="http://maps.google.com/maps/api/staticmap?center='.$img.'&size=400x400&sensor=false&format=png" alt="Map"/></p>';
				}
			
			}else{
				echo trans('NO INTERNET IP');
			}*/
		}else{
			echo trans('LOCAL USER');
		}
		?></td>
	</tr>
</table>
</fieldset>

