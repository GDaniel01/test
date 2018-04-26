<?php
/**
 * Implementation of WorkspaceStatesMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for WorkspaceStatesMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_WorkflowStatesMgr extends SeedDMS_Bootstrap_Style {

	function js() { /* {{{ */
		header('Content-Type: application/javascript; charset=UTF-8');
?>

function checkForm(num)
{
	msg = new Array();

	if($("#name").val() == "") msg.push("<?php printMLText("js_no_name");?>");
	if (msg != "")
	{
  	noty({
  		text: msg.join('<br />'),
  		type: 'error',
      dismissQueue: true,
  		layout: 'topRight',
  		theme: 'defaultTheme',
			_timeout: 1500,
  	});
		return false;
	}
	else
		return true;
}

$(document).ready(function() {
	$('body').on('submit', '#form1', function(ev){
		if(checkForm()) return;
		ev.preventDefault();
	});
	$( "#selector" ).change(function() {
		$('div.ajax').trigger('update', {workflowstateid: $(this).val()});
	});
});
<?php
	} /* }}} */

	function info() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$selworkflowstate = $this->params['selworkflowstate'];

		if($selworkflowstate) {
			if($selworkflowstate->isUsed()) {
				$transitions = $selworkflowstate->getTransitions();
				if($transitions) {
					echo "<table class=\"table table-condensed\">";
					echo "<thead><tr><th>".getMLText('workflow')."</th><th>".getMLText('previous_state')."</th><th>".getMLText('next_state')."</th></tr></thead>\n";
					echo "<tbody>";
					foreach($transitions as $transition) {
						$state = $transition->getState();
						$nextstate = $transition->getNextState();
						$docstatus = $nextstate->getDocumentStatus();
						$workflow = $transition->getWorkflow();
						echo "<tr>";
						echo "<td>";
						echo $workflow->getName();
						echo "</td><td>";
						echo '<i class="icon-circle'.($workflow->getInitState()->getId() == $state->getId() ? ' initstate' : ' in-workflow').'"></i> '.$state->getName();
						echo "</td><td>";
						echo '<i class="icon-circle'.($docstatus == S_RELEASED ? ' released' : ($docstatus == S_REJECTED ? ' rejected' : ' in-workflow')).'"></i> '.$nextstate->getName();
						echo "</td></tr>";
					}
					echo "</tbody>";
					echo "</table>";
				}
			}
		}
	} /* }}} */

	function showWorkflowStateForm($state) { /* {{{ */
		if($state) {
			if($state->isUsed()) {
				$this->infoMsg(getMLText('workflow_state_in_use'));
			} else {
?>
<form class="form-inline" action="../op/op.RemoveWorkflowState.php" method="post">
  <?php echo createHiddenFieldWithKey('removeworkflowstate'); ?>
	<input type="hidden" name="workflowstateid" value="<?php print $state->getID();?>">
	<button type="submit" class="btn"><i class="icon-remove"></i> <?php printMLText("rm_workflow_state");?></button>
</form>
<?php
			}
		}
?>
	<?php	$this->contentContainerStart(); ?>
	<form action="../op/op.WorkflowStatesMgr.php" method="post" class="form-horizontal">
<?php
		if($state) {
			echo createHiddenFieldWithKey('editworkflowstate');
?>
	<input type="Hidden" name="workflowstateid" value="<?php print $state->getID();?>">
	<input type="Hidden" name="action" value="editworkflowstate">
<?php
		} else {
			echo createHiddenFieldWithKey('addworkflowstate');
?>
			<input type="hidden" name="action" value="addworkflowstate">
<?php
		}
		$this->formField(
			getMLText("workflow_state_name"),
			'<input type="text" id="name" name="name" value="'.($state ? htmlspecialchars($state->getName()) : '').'">'
		);
		$html = '
			<select name="docstatus">
				<option value="">'.getMLText('keep_doc_status').'</option>
				<option value="'.S_RELEASED.'" '.(($state && $state->getDocumentStatus() == S_RELEASED) ? "selected" : '').'>'.getMLText('released').'</option>
				<option value="'.S_REJECTED.'" '.(($state && $state->getDocumentStatus() == S_REJECTED) ? "selected" : '').'>'.getMLText('rejected').'</option>';
		$html .= '</select>';
		$this->formField(
			getMLText("workflow_state_docstatus"),
			$html
		);
		$this->formSubmit('<i class="icon-save"></i> '.getMLText("save"));
?>
	</form>
	<?php	$this->contentContainerEnd(); ?>
<?php
	} /* }}} */

	function form() { /* {{{ */
		$selworkflowstate = $this->params['selworkflowstate'];

		$this->showWorkflowStateForm($selworkflowstate);
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$selworkflowstate = $this->params['selworkflowstate'];

		$workflowstates = $dms->getAllWorkflowStates();

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");
		$this->contentHeading(getMLText("workflow_states_management"));
?>

<div class="row-fluid">
	<div class="span4">
		<?php	$this->contentContainerStart(); ?>
			<form class="form-horizontal">
<?php
		$html = '<select id="selector" class="span9">
<option value="-1">'.getMLText("choose_workflow_state").'</option>
<option value="0">'.getMLText("add_workflow_state").'</option>';
		foreach ($workflowstates as $currWorkflowState) {
			$html .= "<option value=\"".$currWorkflowState->getID()."\" ".($selworkflowstate && $currWorkflowState->getID()==$selworkflowstate->getID() ? 'selected' : '').">" . htmlspecialchars($currWorkflowState->getName());
		}
		$html .= '</select>';
		$this->formField(
			getMLText("selection"),
			$html
		);
?>
			</form>
		<?php	$this->contentContainerEnd(); ?>
		<div class="ajax" data-view="WorkflowStatesMgr" data-action="info" <?php echo ($selworkflowstate ? "data-query=\"workflowstateid=".$selworkflowstate->getID()."\"" : "") ?>></div>
	</div>

	<div class="span8">
			<div class="ajax" data-view="WorkflowStatesMgr" data-action="form" <?php echo ($selworkflowstate ? "data-query=\"workflowstateid=".$selworkflowstate->getID()."\"" : "") ?>></div>
	</div>
</div>

<?php
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
