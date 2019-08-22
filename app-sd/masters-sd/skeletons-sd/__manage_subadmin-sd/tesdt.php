<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>View All Activities</h2>
                <div class="nav navbar-right panel_toolbox">
                    <?php if(in_array('delete',$this->Permission)) { ?>
                        <a href="ajax.<?php echo $this->module; ?>.php?action=delete_activity&id=<?php echo $this->id; ?>" class="btn default btn-xs red btn-delete" ><i class="fa fa-trash-o"></i> Delete All Activities</a>
                    <?php } ?>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="actions portlet-toggler">
                    <div class="btn-group"></div>
                </div>
            </div>
            <div class="portlet-body portlet-toggler ">
                <table id="example123_activity" class="table table-striped table-bordered table-hover">
                </table>
            </div>
            <div class="portlet-toggler pageform" style="display:none;"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        OTable = $('#example123_activity').dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "ajax.<?php echo $this->module;?>.php",
            "fnServerData": function (sSource, aoData, fnCallback) {
                $.ajax({
                   "dataType": 'json',
                   "type": "POST",
                   "url": sSource,
                   "data": aoData,
                   "success": fnCallback
                });
             },
             "aoColumns": [
                { "sName": "Activity", 'sTitle' : 'Activity'},
                { "sName": "Date", 'sTitle' : 'Date'}
            ],
            "fnServerParams": function(aoData) {
                setTitle(aoData, this);
                aoData.push({ "name": "action", "value": "activity_datagrid" });
                aoData.push({ "id": "action", "value": "<?php echo $this->id; ?>" });
            },
            "fnDrawCallback": function( oSettings ) {
                $('.make-switch').bootstrapSwitch();
                $('.make-switch').bootstrapSwitch('setOnClass', 'success');
                $('.make-switch').bootstrapSwitch('setOffClass', 'danger');
            }
        });
    });
</script>