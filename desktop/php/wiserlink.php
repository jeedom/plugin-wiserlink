<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('wiserlink');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<div class="row row-overflow">
 <div class="col-lg-12 eqLogicThumbnailDisplay">
   <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
   <div class="eqLogicThumbnailContainer">
   <div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br/>
				<span>{{Ajouter}}</span>
			</div>
  <div class="cursor logoSecondary" id="bt_healthwiserlink">
      <i class="fa fa-medkit"></i>
    <br/>
	<span>{{Santé}}</span>
  </div>
</div>
<legend><i class="icon techno-cable1"></i>  {{Mes Wiserlinks}}
</legend>
 <div class="input-group" style="margin:5px;">
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>
		<div class="input-group-btn">
			<a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
		</div>
	</div>
<div class="eqLogicThumbnailContainer">
  <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
	echo '<img class="lazy" src="plugins/wiserlink/core/config/wiser.png"/>';
	echo "<br/>";
	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
	echo '</div>';
}
?>
</div>
</div>
<div class="col-lg-12 eqLogic" style="display: none;">
 <div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
	  <div class="col-lg-7">
      <form class="form-horizontal">
        <fieldset>
          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
            <div class="col-sm-7">
              <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
              <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
            <div class="col-sm-7">
              <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                <option value="">{{Aucun}}</option>
                <?php
					$options = '';
					foreach ((jeeObject::buildTree(null, false)) as $object) {
					$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
					}
					echo $options;
					?>
             </select>
           </div>
         </div>
         <div class="form-group">
          <label class="col-sm-3 control-label">{{Catégorie}}</label>
          <div class="col-sm-9">
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
        <div class="col-sm-7">
          <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
          <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Adresse IP}}</label>
        <div class="col-sm-7">
          <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="addr" placeholder="{{Adresse IP}}"/>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Login}}</label>
        <div class="col-sm-7">
          <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="user" placeholder="{{Login}}"/>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Mot de passe}}</label>
        <div class="col-sm-7">
          <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pwd" placeholder="{{Mot de passe}}"/>
        </div>
      </div>
    </fieldset>
  </form>
</div>
<div class="col-lg-5">
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
