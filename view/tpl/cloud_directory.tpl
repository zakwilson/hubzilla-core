<div class="section-content-wrapper-np">
	{{if $tiles}}
	<table id="cloud-index">
		<tr id="new-upload-progress-bar-1"></tr> {{* this is needed to append the upload files in the right order *}}
	</table>

	{{if $parentpath}}
	<div class="cloud-container" >
		<div class="cloud-icon tiles">
			<a href="{{$parentpath}}">
				<div class="cloud-icon-container">
					<i class="fa fa-fw fa-level-up" ></i>
				</div>
			</a>
		</div>
		<div class="cloud-title">
			<a href="{{$parentpath}}">..</a>
		</div>
	</div>
	{{/if}}

	{{foreach $entries as $item}}
	<div class="cloud-container">
		<div class="cloud-icon tiles"><a href="{{$item.rel_path}}">
		{{if $item.photo_icon}}
		<img src="{{$item.photo_icon}}" title="{{$item.type}}" >
		{{else}}
		<div class="cloud-icon-container">
			<i class="fa fa-fw {{$item.icon_from_type}}" title="{{$item.type}}"></i>
		</div>
		{{/if}}
		</div>
		<div class="cloud-title">
			<a href="{{$item.rel_path}}">
				{{$item.name}}
			</a>
		</div>
		{{if $item.is_owner}}
			{{* add file tools here*}}
		{{/if}}
	</div>
	{{/foreach}}
	<div class="clear"></div>
	{{else}}
	<table id="cloud-index">
		<tr>
			<th width="1%">{{* multi tool checkbox *}}</th>
			<th width="1%">{{* icon *}}</th>
			<th width="93%">{{$name}}</th>
			<th width="1%">{{* categories *}}</th>
			<th width="1%">{{* lock icon *}}</th>
			<th width="1%">{{* tools icon *}}</th>
			<th width="1%" class="d-none d-md-table-cell">{{$size}}</th>
			<th width="1%" class="d-none d-md-table-cell">{{$lastmod}}</th>
		</tr>
		{{if $parentpath}}
		<tr id="cloud-index-up" class="cloud-index{{if ! $is_root_folder}} attach-drop{{/if}}"{{if ! $is_root_folder}} data-folder="{{$folder_parent}}"/{{/if}}>
			<td></td>
			<td><i class="fa fa-level-up"></i></td>
			<td colspan="6"><a href="{{$parentpath}}" title="{{$parent}}" class="p-2" draggable="false">..</a></td>
		</tr>
		<tr class="cloud-tools">
			<td colspan="8" class="attach-edit-panel">{{* this is for display consistency *}}</td>
		</tr>
		{{/if}}
		{{if $channel_id && $is_owner && $entries.0}}
		<tr id="cloud-multi-actions">
			<td colspan="2">
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="checkbox" id="cloud-multi-tool-select-all" value="" title="Select all">
				</div>
			</td>
			<td colspan="3">
				<div class="form-check form-check-inline">
					<label class="form-check-label" for="cloud-multi-tool-select-all">Select all</label>
				</div>
			</td>
			<td colspan="3">
				{{if $is_owner}}
				<div class="dropdown">
					<button class="btn btn-warning btn-sm" id="multi-dropdown-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fa fa-fw fa-ellipsis-v d-table-cell"></i><span class="d-none d-md-table-cell">Bulk Actions</span>
					</button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-button">
						{{if $is_owner}}
						<a id="cloud-multi-tool-perms-btn" class="dropdown-item" href="#"><i class="fa fa-fw fa-lock"></i> Adjust permissions</a>
						{{/if}}
						<a id="cloud-multi-tool-move-btn" class="dropdown-item" href="#"><i class="fa fa-fw fa-copy"></i> Move or copy</a>
						<a id="cloud-multi-tool-categories-btn" class="dropdown-item" href="#"><i class="fa fa-fw fa-asterisk"></i> Categories</a>
						<a id="cloud-multi-tool-delete-btn" class="dropdown-item" href="#"><i class="fa fa-fw fa-trash-o"></i> {{$delete}}</a>
					</div>
				</div>
				{{else if $is_admin}}
				<div class="dropdown">
					<button class="btn btn-warning btn-sm" id="multi-dropdown-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fa fa-fw fa-ellipsis-v d-table-cell"></i><span class="d-none d-md-table-cell">Bulk Actions</span>
					</button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-button">
						<a id="cloud-multi-tool-delete-btn" class="dropdown-item" href="#"><i class="fa fa-fw fa-trash-o"></i> {{$admin_delete}}</a>
					</div>
				</div>
				{{/if}}
			</td>
		</tr>
		<tr id="cloud-multi-tools">
			<td id="attach-multi-edit-panel" colspan="8">
				<form id="attach_multi_edit_form" action="attach_edit" method="post" class="acl-form" data-form_id="attach_multi_edit_form" data-allow_cid='{{$allow_cid}}' data-allow_gid='{{$allow_gid}}' data-deny_cid='{{$deny_cid}}' data-deny_gid='{{$deny_gid}}'>
					<input type="hidden" name="channel_id" value="{{$channel_id}}" />
					<input id="multi-perms" type="hidden" name="permissions" value="0">
					<input type="hidden" name="return_path" value="{{$return_path}}">
					<div id="cloud-multi-tool-move" class="cloud-multi-tool">
						{{include file="field_select.tpl" field=$newfolder}}
						{{include file="field_checkbox.tpl" field=$copy}}
					</div>
					<div id="cloud-multi-tool-categories" class="cloud-multi-tool">
						{{include file="field_input.tpl" field=$categories}}
					</div>
					<div id="cloud-multi-tool-submit" class="cloud-multi-tool">
						{{if $is_owner}}
						{{include file="field_checkbox.tpl" field=$recurse}}
						{{/if}}
						<div id="attach-multi-submit" class="form-group">
							<button id="cloud-multi-tool-cancel-btn" class="btn btn-outline-secondary btn-sm cloud-multi-tool-cancel-btn" type="button">
									Cancel
							</button>
							<div id="attach-multi-edit-perms" class="btn-group float-right">
								{{if $is_owner}}
								<button id="multi-dbtn-acl" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#aclModal" title="{{$permset}}" type="button">
									<i id="multi-jot-perms-icon" class="fa fa-{{$lockstate}} jot-icons jot-perms-icon"></i>
								</button>
								{{/if}}
								<button id="multi-dbtn-submit" class="btn btn-primary btn-sm" type="submit" name="submit">
									{{$edit}}
								</button>
							</div>
						</div>
					</div>
				</form>
			</td>
		</tr>
		{{/if}}
		<tr id="new-upload-progress-bar-1"></tr> {{* this is needed to append the upload files in the right order *}}
		{{foreach $entries as $item}}
		<tr id="cloud-index-{{$item.attach_id}}" class="cloud-index{{if $item.collection}} attach-drop{{/if}}"{{if $item.collection}} data-folder="{{$item.resource}}"{{/if}} data-id="{{$item.attach_id}}" draggable="true">
			<td>
				{{if $channel_id && $is_owner}}
				<div class="form-check form-check-inline">
					<input class="form-check-input cloud-multi-tool-checkbox" type="checkbox" id="cloud-multi-tool-checkbox-{{$item.attach_id}}" name="attach_ids[]" value="{{$item.attach_id}}">
				</div>
				{{/if}}
			</td>
			<td><i class="fa {{$item.icon_from_type}}" title="{{$item.type}}"></i></td>
			<td><a href="{{$item.rel_path}}" class="p-2" draggable="false">{{$item.name}}</a></td>
			<td>{{$item.terms}}</td>
			<td class="cloud-index-tool p-2">{{if $item.lockstate == 'lock'}}<i class="fa fa-fw fa-{{$item.lockstate}}"></i>{{/if}}</td>
			<td class="cloud-index-tool">
				{{if ($item.is_owner || $item.is_creator) && $item.attach_id}}
				<div class="dropdown">
					<button class="btn btn-link btn-sm" id="dropdown-button-{{$item.attach_id}}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fa fa-fw fa-ellipsis-v"></i>
					</button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-button-{{$item.attach_id}}">
						{{if $item.is_owner}}
						<a id="cloud-tool-perms-btn-{{$item.attach_id}}" class="dropdown-item cloud-tool-perms-btn" href="#" data-id="{{$item.attach_id}}"><i class="fa fa-fw fa-{{$item.lockstate}}"></i> Adjust permissions</a>
						{{/if}}
						<a id="cloud-tool-rename-btn-{{$item.attach_id}}" class="dropdown-item cloud-tool-rename-btn" href="#" data-id="{{$item.attach_id}}"><i class="fa fa-fw fa-pencil"></i> Rename</a>
						<a id="cloud-tool-move-btn-{{$item.attach_id}}" class="dropdown-item cloud-tool-move-btn" href="#" data-id="{{$item.attach_id}}"><i class="fa fa-fw fa-copy"></i> Move or copy</a>
						<a id="cloud-tool-categories-btn-{{$item.attach_id}}" class="dropdown-item cloud-tool-categories-btn" href="#" data-id="{{$item.attach_id}}"><i class="fa fa-fw fa-asterisk"></i> Categories</a>
						{{if !$item.collection}}
						{{if $item.is_owner}}
						<a id="cloud-tool-share-btn-{{$item.attach_id}}" class="dropdown-item cloud-tool-share-btn" href="/rpost?attachment=[attachment]{{$item.resource}},{{$item.revision}}[/attachment]&acl[allow_cid]={{$item.raw_allow_cid}}&acl[allow_gid]={{$item.raw_allow_gid}}&acl[deny_cid]={{$item.raw_deny_cid}}&acl[deny_gid]={{$item.raw_deny_gid}}" data-id="{{$item.attach_id}}"><i class="fa fa-fw fa-share-square-o"></i> Post</a>
						{{/if}}
						<a id="cloud-tool-download-btn-{{$item.attach_id}}" class="dropdown-item cloud-tool-download-btn" href="/attach/{{$item.resource}}" data-id="{{$item.attach_id}}"><i class="fa fa-fw fa-cloud-download"></i> Download</a>
						{{/if}}
						<a id="cloud-tool-delete-btn-{{$item.attach_id}}" class="dropdown-item cloud-tool-delete-btn" href="#" data-id="{{$item.attach_id}}"><i class="fa fa-fw fa-trash-o"></i> {{$delete}}</a>
					</div>
				</div>
				{{else}}
				{{if ($is_admin || !$item.collection) && $item.attach_id}}
				<div class="dropdown">
					<button class="btn btn-link btn-sm" id="dropdown-button-{{$item.attach_id}}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fa fa-fw fa-ellipsis-v"></i>
					</button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-button-{{$item.attach_id}}">
						{{if !$item.collection}}
						<a id="cloud-tool-download-btn-{{$item.attach_id}}" class="dropdown-item cloud-tool-download-btn" href="/attach/{{$item.resource}}" data-id="{{$item.attach_id}}"><i class="fa fa-fw fa-cloud-download"></i> Download</a>
						{{/if}}
						{{if $is_admin}}
						<a id="cloud-tool-delete-btn-{{$item.attach_id}}" class="dropdown-item cloud-tool-delete-btn" href="#" data-id="{{$item.attach_id}}"><i class="fa fa-fw fa-trash-o"></i> {{$admin_delete}}</a>
						{{/if}}
					</div>
				</div>
				{{/if}}
			</td>
			{{/if}}
			<td class="d-none d-md-table-cell p-2">{{$item.size_formatted}}</td>
			<td class="d-none d-md-table-cell p-2">{{$item.last_modified}}</td>
		</tr>
		<tr id="cloud-tools-{{$item.attach_id}}" class="cloud-tools">
			<td id="attach-edit-panel-{{$item.attach_id}}" class="attach-edit-panel" colspan="8">
				<form id="attach_edit_form_{{$item.attach_id}}" action="attach_edit" method="post" class="acl-form" data-form_id="attach_edit_form_{{$item.attach_id}}" data-allow_cid='{{$item.allow_cid}}' data-allow_gid='{{$item.allow_gid}}' data-deny_cid='{{$item.deny_cid}}' data-deny_gid='{{$item.deny_gid}}'>
					<input type="hidden" name="attach_id" value="{{$item.attach_id}}" />
					<input type="hidden" name="channel_id" value="{{$channel_id}}" />
					<input type="hidden" name="return_path" value="{{$return_path}}">
					<div id="cloud-tool-rename-{{$item.attach_id}}" class="cloud-tool">
						{{include file="field_input.tpl" field=$item.newfilename}}
					</div>
					<div id="cloud-tool-move-{{$item.attach_id}}" class="cloud-tool">
						{{include file="field_select.tpl" field=$item.newfolder}}
						{{include file="field_checkbox.tpl" field=$item.copy}}
					</div>
					<div id="cloud-tool-categories-{{$item.attach_id}}" class="cloud-tool">
						{{include file="field_input.tpl" field=$item.categories}}
					</div>
					<div id="cloud-tool-submit-{{$item.attach_id}}" class="cloud-tool">
						{{if $item.is_owner}}
						{{if !$item.collection}}{{include file="field_checkbox.tpl" field=$item.notify}}{{/if}}
						{{if $item.collection}}{{include file="field_checkbox.tpl" field=$item.recurse}}{{/if}}
						{{/if}}
						<div id="attach-submit-{{$item.attach_id}}" class="form-group">
							<button id="cloud-tool-cancel-btn-{{$item.attach_id}}" class="btn btn-outline-secondary btn-sm cloud-tool-cancel-btn" type="button" data-id="{{$item.attach_id}}">
									Cancel
							</button>
							<div id="attach-edit-perms-{{$item.attach_id}}" class="btn-group float-right">
								{{if $item.is_owner}}
								<button id="dbtn-acl-{{$item.attach_id}}" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#aclModal" title="{{$permset}}" type="button">
									<i id="jot-perms-icon-{{$item.attach_id}}" class="fa fa-{{$item.lockstate}} jot-icons jot-perms-icon"></i>
								</button>
								{{/if}}
								<button id="dbtn-submit-{{$item.attach_id}}" class="btn btn-primary btn-sm" type="submit" name="submit">
									{{$edit}}
								</button>
							</div>
						</div>
					</div>
					<!--div id="cloud-tool-share-{{$item.attach_id}}" class="">
						<div id="attach-edit-tools-share-{{$item.attach_id}}" class="btn-group form-group">
							<button id="link-btn-{{$item.attach_id}}" class="btn btn-outline-secondary btn-sm" type="button" onclick="openClose('link-code-{{$item.attach_id}}');" title="{{$link_btn_title}}">
								<i class="fa fa-link jot-icons"></i>
							</button>
						</div>
					</div>
					{{if !$item.collection}}
					<a href="/rpost?attachment=[attachment]{{$item.resource}},{{$item.revision}}[/attachment]" id="attach-btn" class="btn btn-outline-secondary btn-sm" title="{{$attach_btn_title}}">
						<i class="fa fa-share-square-o jot-icons"></i>
					</a>
					{{/if}}
					<div id="link-code-{{$item.attach_id}}" class="form-group link-code">
						<label for="linkpasteinput-{{$item.attach_id}}">{{$cpldesc}}</label>
						<input type="text" class="form-control" id="linkpasteinput-{{$item.attach_id}}" name="linkpasteinput-{{$item.attach_id}}" value="{{$item.full_path}}" onclick="this.select();"/>
					</div-->
				</form>
			</td>
		</tr>
		{{/foreach}}
	</table>
{{/if}}
</div>
