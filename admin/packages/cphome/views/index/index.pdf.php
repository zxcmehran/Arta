<?php 
if(!defined('ARTA_VALID')){die('No access');}
class CphomeViewIndex extends ArtaPackageView{
	
	function display(){
		$model=$this->getModel();
		$pdf=ArtaLoader::PDF();
		if(getVar('note')!=='admin'){
			$user=ArtaLoader::User();
			$user=$user->getCurrentUser();
			$pdf->SetAuthor($user->username.' ('.$user->name.')');
			$pdf->SetTitle(trans('USER NOTES'));
			$pdf->AddHeader(trans('MODIFIED').": ".ArtaDate::_($model->getUserModified()));
			echo nl2br(htmlspecialchars($model->getUserData()));
		}else{
			$pdf->SetAuthor('--');
			$pdf->SetTitle(trans('ADMIN NOTES'));
			$pdf->AddHeader(trans('MODIFIED').": ".ArtaDate::_($model->getAdminModified()));
			echo nl2br(htmlspecialchars($model->getAdminData()));
		}
	}
	

}
?>