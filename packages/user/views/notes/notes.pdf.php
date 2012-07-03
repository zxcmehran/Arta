<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/18 13:9 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
 
class UserViewNotes extends ArtaPackageView{
	function display(){
		$pdf=ArtaLoader::PDF();
		$u=$this->getCurrentUser();
		if($u->id!==0){
			$pdf->SetAuthor($u->username.' ('.$u->name.')');
			$pdf->SetTitle(trans('YOUR PERSONAL NOTES'));
			$pdf->AddHeader(trans('LAST MODIFIED')." ".ArtaDate::_(ArtaUserHelper::getModified($u->id, 'id')));
			echo nl2br(htmlspecialchars(ArtaUserHelper::getText($u->id, 'id')));
		}else{
			ArtaError::show('403');
		}
	}
}

?>