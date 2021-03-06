<!-- BEGIN: main -->
<div id="add">
	<table class="tab1">
		<colgroup>
			<col class="w50"/>
			<col>
		</colgroup>
		<thead>
			<tr>
				<td>&nbsp;</td>
				<td>{LANG.name}</td>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="center"><input name="checkall" type="checkbox"/></td>
				<td>
				<select name="topicsid">
					<!-- BEGIN: topicsid -->
					<option value="{TOPICSID.key}">{TOPICSID.title}</option>
					<!-- END: topicsid -->
				</select><input name="update" type="button" value="{LANG.save}" /></td>
			</tr>
		</tfoot>
		<tbody>
			<!-- BEGIN: loop -->
			<tr>
				<td class="center"><input type="checkbox" value="{ROW.id}" name="idcheck"{ROW.checked}></td>
				<td>{ROW.title}</td>
			</tr>
			<!-- END: loop -->
		</tbody>
	</table>
</div>
<script type="text/javascript">
	$(function() {
	    function checkallfirst() {
	        $(this).one("click", checkallsecond);
			$('input:checkbox').each(function() {
				$(this).attr('checked', 'checked');
			});
	    }
	    function checkallsecond() {
	        $(this).one("click", checkallfirst);
			$('input:checkbox').each(function() {
				$(this).removeAttr('checked');
			});
	    }
	    $('input[name=checkall]').one("click", checkallfirst);

		$('input[name=update]').click(function() {
			var listid = [];
			$('input[name=idcheck]:checked').each(function() {
				listid.push($(this).val());
			});
			if (listid.length < 1) {
				alert('{LANG.topic_nocheck}');
				return false;
			}
			var topic = $('select[name=topicsid]').val();
			$.ajax({
				type : 'POST',
				url : 'index.php?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=addtotopics',
				data : 'listid=' + listid + '&topicsid=' + topic,
				success : function(data) {
					alert(data);
					window.location = 'index.php?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=topics';
				}
			});
			return false;
		});
	});
</script>
<!-- END: main -->