<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('wiserlink');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<div class="row row-overflow">
  <div class="col-lg-2">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un dispositif}}</a>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
foreach ($eqLogics as $eqLogic) {
	echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
     </ul>
   </div>
 </div>
 <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
   <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
   <div class="eqLogicThumbnailContainer">
    <div class="cursor eqLogicAction" data-action="add" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
      <i class="fa fa-plus-circle" style="font-size : 5em;color:#94ca02;"></i>
    <br>
    <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;;color:#94ca02">{{Ajouter}}</span>
  </div>
  <div class="cursor" id="bt_healthwiserlink" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
      <i class="fa fa-medkit" style="font-size : 5em;color:#767676;"></i>
    <br>
    <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676">{{Santé}}</span>
  </div>
</div>
<legend><i class="icon techno-cable1"></i>  {{Mes Wiserlinks}}
</legend>
<div class="eqLogicThumbnailContainer">
  <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = '';
	if ($eqLogic->getIsEnable() != 1) {
		$opacity = 'opacity:0.3;';
	}
	echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="text-align: center; background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
	echo '<img src="plugins/wiserlink/core/config/wiser.png" height="105" width="105" />';
	echo "<br>";
	echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;">' . $eqLogic->getHumanName(true, true) . '</span>';
	echo '</div>';
}
?>
</div>
</div>
<div class="col-lg-10 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
  <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
  <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
  <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
	  <div class="col-sm-6">
      <form class="form-horizontal">
        <fieldset>
          <div class="form-group">
            <label class="col-lg-3 control-label">{{Nom de l'équipement}}</label>
            <div class="col-lg-4">
              <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
              <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-lg-3 control-label" >{{Objet parent}}</label>
            <div class="col-lg-4">
              <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                <option value="">{{Aucun}}</option>
                <?php
foreach (object::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
             </select>
           </div>
         </div>
         <div class="form-group">
          <label class="col-lg-3 control-label">{{Catégorie}}</label>
          <div class="col-lg-9">
            <?php
foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
	echo '<label class="checkbox-inline">';
	echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
	echo '</label>';
}
?>
         </div>
       </div>
       <div class="form-group">
        <label class="col-sm-3 control-label"></label>
        <div class="col-sm-9">
          <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
          <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 control-label">{{Adresse IP}}</label>
        <div class="col-lg-4">
          <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="addr" placeholder="{{Adresse IP}}"/>
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 control-label">{{Login}}</label>
        <div class="col-lg-4">
          <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="user" placeholder="{{Login}}"/>
        </div>
      </div>
      <div class="form-group">
        <label class="col-lg-3 control-label">{{Mot de passe}}</label>
        <div class="col-lg-4">
          <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pwd" placeholder="{{Mot de passe}}"/>
        </div>
      </div>
    </fieldset>
  </form>
</div>
<div class="col-sm-6">
<center>
  <img src="plugins/wiserlink/core/config/wiser.png" data-original=".png" id="img_device" class="img-responsive" style="max-height : 400px;"/>
</center>
</div>
</div>
<div role="tabpanel" class="tab-pane" id="commandtab">
 <table id="table_cmd" class="table table-bordered table-condensed">
   <thead>
    <tr>
      <th style="width: 600px;">{{Nom}}</th><th>{{Options}}</th><th>{{Action}}</th>
    </tr>
  </thead>
  <tbody>

  </tbody>
</table>
</div>
</div>
</div>
</div>

<?php include_file('desktop', 'wiserlink', 'js', 'wiserlink');?>
<?php include_file('core', 'plugin.template', 'js');?>
