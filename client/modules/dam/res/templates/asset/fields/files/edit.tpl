<div class="attachment-upload">
    <div class="clearfix attachment-control">
        {{#unless uploadFromFileSystemDisabled}}
        <div class="pull-left">
            <label class="attach-file-label" title="{{translate 'Attach File'}}">
                <span class="btn btn-default btn-icon"><span class="glyphicon glyphicon-paperclip"></span></span>
                <input type="file" class="file pull-right" multiple>
            </label>
        </div>
        {{/unless}}

        {{#if sourceList.length}}
        <div class="pull-left dropdown">
            <button class="btn btn-default btn-icon dropdown-toggle" type="button" data-toggle="dropdown">
                <span class="fas fa-file fa-sm"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
            {{#each sourceList}}
                <li><a href="javascript:" class="action" data-action="insertFromSource" data-name="{{./this}}">{{translate this category='insertFromSourceLabels' scope='Attachment'}}</a></li>
            {{/each}}
            </ul>
        </div>
        {{/if}}
    </div>
    {{#if isUploading}}
    <div class="progress">
        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0"
             aria-valuemin="0" aria-valuemax="100" style="width:{{percentCompleted}}%">
            {{percentCompleted}}% {{translate 'uploaded' category='labels' scope='ImportFeed'}}
        </div>
    </div>
    {{/if}}
    <div class="attachments"></div>
</div>
<style>
    .attachment-upload .attachments {
        max-height: 450px;
        overflow: auto;
    }
    .attachment-upload .progress {
        width: 50%;
    }
</style>
