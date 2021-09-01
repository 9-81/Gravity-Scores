<div class="copy-tooltip success" style="display:none;">
    <p>The Shortcode has been copied to your clipboard.</p>
</div>

<div class="copy-tooltip error" style="display:none;">
    <p>Copying the shortcode failed.</p>
</div>



<ul class="title-row">
    <li :class="{'active': !editMode }" class="available" @click="editMode=false;saved=false">All Evaluations</li>
    <li :class="{'active':  editMode, 'available': edit !== undefined }" @click="editMode= (edit !== undefined); saved=false">Edit {{ (edit == undefined) ? 'Evaluation' : edit.title }}</li>
</ul>

<!-- LIST -->
<div class="tab-section tableSection" v-if="!editMode">
    
    <p v-if="evaluations.length > 0">
        Evaluations <strong>connect one or more subscales</strong> of the corresponding tests <strong>to a frontend-representation</strong> like a chart.
    </p>
    
    <div v-if="evaluations.length === 0">
        <p>There are <strong>no evaluations yet</strong>. Would you like to add a new evaluation instead?</p>
        <ul class="new_evaluation_button">
            <li><a href="admin.php?page=gravityscores_evaluation">Add new Evaluation</a></li>
        </ul>
    </div>

<div class="flex-row" v-if="evaluations.length > 0">
    <list-table :columns="{
    'title': {
      label: 'Evaluation'
    },
    'visualization': {
      label: 'Visualization'
    },
    'id': {
      label: 'Evaluation ID',
      //sortable: true
    }
    
  }"
  :rows="evaluations"
  :actions="[
    {
      key: 'shortcode',
      label: 'Shortcode'
    },
    {
      key: 'edit',
      label: 'Edit'
    },
    {
      key: 'delete',
      label: 'Delete'
    },

  ]"
  :show-cb="true"
  :total-items="evaluations.length"
  :bulk-actions="[
    {
      key: 'delete',
      label: 'Delete'
    }
  ]"
  action-column="title"
  @action:click="all_evaluations_table_actions"
  @bulk:click="onBulkAction"
  ></list-table>
</div>

</div>

<!-- EDIT OVERVIEW -->
<div class="tab-section sectionOverview" v-if="editMode && editSection == 0">
<span style="float:right; display:inline-block; background-color:lightgrey; color:#0073aa; padding: 0.3rem 1rem; border-radius:1rem; margin-left:1rem; margin-top:1rem;">Id: {{ edit.id }}</span>    
    
    
    <div>
    

    <div v-if="saved" style="background-color: #abff9a; font-size: 1.3rem; width: calc(100% - 2rem); padding: 1rem; border: thin solid #182e18; text-align:center;">
        <span>Your changes have been saved.</span>
    </div>
    
    <p>What do you want to <strong>modify</strong> about the <strong>'{{ edit.title }}'</strong> Evaluation?</p>

    <ul class="evaluation-edit-sections">
        <li tabindex="0" @mouseover="$event.srcElement.focus()" @click="editSection=1" @keydown.enter="editSection=1"> Rename '{{ edit.title }}'</li>
        <li tabindex="0" @mouseover="$event.srcElement.focus()" @click="editSection=2" @keydown.enter="editSection=2"> Select another visualization </li>
        <li tabindex="0" @mouseover="$event.srcElement.focus()" @click="editSection=3" @keydown.enter="editSection=3"> Change subscales</li>
    </ul>
    <br />
    <button class="back-button" @click="editMode=false;saved=false" @keydown.enter="editMode=false;saved=false"> &larr; &nbsp; Return to the Evaluations List</button>
</div>
</div>


<div class="tab-section sectionRename" v-if="editMode && editSection == 1">
<span style="float:right; display:inline-block; background-color:lightgrey; color:#0073aa; padding: 0.3rem 1rem; border-radius:1rem; margin-left:1rem; margin-top:1rem;">Id: {{ edit.id }}</span>    
<div>
    
    <p>
        Please enter a <strong>new name</strong> for the Evaluation.
    </p>

    <p style="margin: 2rem 0;">
        <input id="rename-evaluation-text" type="text" v-model="currentTitle" @keypress.enter="document.querySelector('#rename-evaluation-button').click()" :placeholder="edit.title" autofocus/>
        <button id="rename-evaluation-button" @click="renameEvaluation()" :disabled="currentTitle.trim()==edit.title.trim()">rename</button>
    </p>
    

    <button class="back-button" @click="editSection=0;saved=false" @keydown.enter="editSection=0;saved=false">&larr; &nbsp; Return to Edit Overview</button>

</div>
</div>


<div class="tab-section sectionVisualizations" v-if="editMode && editSection == 2">
<span style="float:right; display:inline-block; background-color:lightgrey; color:#0073aa; padding: 0.3rem 1rem; border-radius:1rem; margin-left:1rem; margin-top:1rem;">Id: {{ edit.id }}</span>    
<div>

    
    <p>
        Please <strong>select a Visualization</strong> for the '{{ edit.title }}' Evaluation.
    </p>
    <p>
        The <strong>'{{ edit.visualization }}</strong>' visualization <strong>is currently active</strong>.
    </p>

    <p style="margin: 2rem 0;">

        <select id="select-evaluation-visualization" v-model="selectedVisualization">
            <option v-for="visualization in visualizations" :value="visualization.id" :selected="visualization.id==edit.visualization_id" :disabled="visualization.id==edit.visualization_id">{{ visualization.name }}</option>
        </select>
        <button id="select-evaluation-visualization-button" @click="changeVisualization()" :disabled="selectedVisualization==edit.visualization_id">save</button>
    </p>
    

    <button class="back-button" @click="editSection=0;saved=false" @keydown.enter="editSection=0;saved=false">&larr; &nbsp; Return to Edit Overview</button>

</div>
</div>


<div class="tab-section sectionSubscales" v-if="editMode && editSection == 3">

    

    <span style="float:right; display:inline-block; background-color:lightgrey; color:#0073aa; padding: 0.3rem 1rem; border-radius:1rem; margin-left:3rem; margin-top:1rem;">Id: {{ edit.id }}</span>    
    
    <p>Please <strong>check the subscales</strong> this evaluation is supposed to represent.
    You can create evaluations using subscales of multiple tests at once. 
    Add a test first, to add its subscales. Tests with no subscales checked are removed from the evaluation.</p>
   
    <ul>
        <li class="testListButton" v-for="test in editTests">{{ test.form_title }}</li>
        <li class="testListButton testListButtonAdd" @click="testSelectorActive=true"> + </li>
    

        <div class="selectTestOverlay" v-if="testSelectorActive">
           
        <input type="text" class="searchTests" v-model="searchTerm" :placeholder="(editTests.length > 0) ? 'find e.g. ' + editTests[0].form_title + ' ...': 'find ...'"  autofocus />
        <div>
            <ul>
                    <li v-for="test in filteredTests" class="addableTest" @click="addTest(test);">{{ test.form_title }}</li>
                </ul>
            </div>
        </div>
    </ul>

    


    <hr />

    <div v-for="test in editTests">
    <h1>{{ test.form_title }}</h1>
    <ul>
        <li v-for="subscale in test.subscales" style="line-heigt: 2;">
            <input :id="'subscale_' + subscale.id" type="checkbox" :checked="edit.subscale_ids.includes(subscale.id)" @change="toggleSubscale(subscale)" style="line-heigt: 2;" />
            <label :for="'subscale_' + subscale.id" style="line-heigt: 2;" >{{ subscale.name }}</label>
        </li>
    </ul>
    </div>

    
    <br /><br />

    <button id="add-more-tests-button" @click="saveSubscales()" @keydown.enter="saveSubscales()">Save</button><br /><br />
    <button class="back-button" @click="editSection=0;saved=false" @keydown.enter="editSection=0;saved=false">&larr; &nbsp; Return to Edit Overview</button>

</div>
