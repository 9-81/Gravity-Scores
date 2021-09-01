


<ul class="title-row">
    <li :class="{'active': tabIsActive(0), 'available': true }" @click="setTabActive(0)">All Tests</li>
    <li :class="{'active': tabIsActive(1), 'available': tabIsAvailable(1) }" @click="setTabActive(1)">{{ tabIsAvailable(1) ?  edit.form_title : 'Single Test' }}</li>
    <li :class="{'active': tabIsActive(2), 'available': tabIsAvailable(2) }" @click="setTabActive(2)"> {{ editSubscaleTabName }}</li>
</ul>


<!-- WIZARD STEP 0 -->
<div class="tab-section" v-if="tabIsActive(0)">

<div>
    <p class="section-description" v-if="tests.length>0">
        Tests are build on forms in Gravity Forms.
        They <strong>categorize questions into subscales</strong> and <strong>provide metadata</strong>, which can then be used to create evaluations over those subscales.
    </p> 
    <p class="section-description" v-if="tests.length === 0">
        There are <strong>no tests yet.</strong> Would you like to add a new test instead?
        <ul class="new_test_button" v-if="tests.length === 0">
            <li><a href="admin.php?page=gravityscores_test">Add new Test</a></li>
        </ul>
    </p>
</div>
    <list-table :columns="{
    'form_title': {
      label: 'Title',
      sortable: true
    },
    'id': {
      label: 'Test ID'
    },
    'form_id': {
      label: 'Form ID'
    }
  }"
  :rows="tests"
  :actions="[
    {
      key: 'edit',
      label: 'Edit'
    },
    {
      key: 'delete',
      label: 'Delete'
    }
  ]"
  :show-cb="true"
  :total-items="tests.length"
  :bulk-actions="[
    {
      key: 'delete',
      label: 'Delete'
    }
  ]"
  action-column="form_title"
  @action:click="all_tests_table_actions"
  @bulk:click="onBulkAction"
  ></list-table>


</div>


<div class="tab-section" v-if="tabIsActive(1)">

<p class="section-description" v-if="tests.length>0">
        
        <strong>Subscales</strong> are categories <strong>of a test,</strong> that can be evaluated against each other.
        Currently <strong v-if="edit !== undefined">"{{ edit.form_title }}"</strong>  is shown.
    </p> 

<list-table :columns="{
    'name': {
      label: 'Subscale'
    },
    'id': {
      label: 'Subscale Id'
    },
    'evaluables_length': {
      label: 'Evaluables',
    },
    'group_results_length': {
      label: 'Connected Comparison Groups',
    },
    
  }"
  :rows="edit_subscales"
  :actions="[
    {
      key: 'edit',
      label: 'Edit'
    },
    {
      key: 'delete',
      label: 'Delete'
    }
  ]"
  :show-cb="true"
  :total-items="tests.length"
  :bulk-actions="[
    {
      key: 'delete',
      label: 'Delete'
    }
  ]"
  action-column="name"
  @action:click="subscales_table_actions"
  @bulk:click="onBulkAction"
  ></list-table>

  <button v-if="edit !== undefined" @click="newSubscaleMode=true;currentSubscaleName='';activeSubscaleId=undefined;editSubscaleMode=true; setTabActive(2)" style="padding:0.7rem;  background-color:white; cursor:pointer; font-weight:500; color:#0073aa; width:100%; margin:1rem 0; border:thin solid #AAA;">Add new Subscale </button>
  <button class="back-button" @click="setTabActive(0)" @keydown.enter="setTabActive(0)"> &larr; &nbsp; Return to the Test List</button>


</div>

<div class="tab-section bg-white" v-if="tabIsActive(2) && editSection==0 && activeSubscaleId!==undefined">

<div style="width:fit-content;">
    <p v-if="edit != undefined">What do you want to <strong>modify</strong> about <strong>'{{  activeSubscale.name }}' on '{{ edit.form_title }}'</strong>?</p>

      <ul class="test-edit-sections">
          <li tabindex="0" @mouseover="$event.srcElement.focus()" @click="editSection=1" @keydown.enter="editSection=1"> Rename Subscale </li>
          <li tabindex="0" @mouseover="$event.srcElement.focus()" @click="editSection=2" @keydown.enter="editSection=2"> Change Description </li>
          <li tabindex="0" @mouseover="$event.srcElement.focus()" @click="editSection=3" @keydown.enter="editSection=3"> Change Subscale Questions</li>
          <li tabindex="0" @mouseover="$event.srcElement.focus()" @click="editSection=4" @keydown.enter="editSection=4"> Change Subscale Data</li>
      </ul>
      <br />
      <button class="back-button" @click="setTabActive(1)" @keydown.enter="setTabActive(1)"> &larr; &nbsp; Return to {{ edit.form_title }}</button>

  </div>

</div>

<div class="tab-section bg-white" v-if="tabIsActive(2) && editSection==1 && activeSubscaleId!==undefined">
    
    <div class="fit-content">
    <p>
        Please enter a <strong>new name</strong> for <strong>'{{ activeSubscale.name.trim() }}' on {{ edit.form_title }}.</strong>
    </p>

    <p style="margin: 2rem 0;">
        <input id="rename-subscale-text" type="text" v-model="currentSubscaleName" @keypress.enter="document.querySelector('#rename-subscale-button').click()" :placeholder="activeSubscale.name" autofocus/>
        <button id="rename-subscale-button" @click="renameSubscale()" :disabled="currentSubscaleName.trim()==activeSubscale.name.trim()||currentSubscaleName.trim()===''">rename</button>
    </p>
    
    <button class="back-button" @click="editSection=0;setTabActive(2)" @keydown.enter="">&larr; &nbsp; Return to Subscale Edit Overview</button>

  </div>
</div>
<div class="tab-section  bg-white" v-if="tabIsActive(2) && editSection==2 && !newSubscaleMode">
    
<div class="fit-content">
    <p>
        Please enter a <strong>new description</strong> for <strong>'{{ activeSubscale.name.trim() }}' on {{ edit.form_title }}.</strong>
    </p>
    
    <textarea id="describe-subscale-text" type="text" v-model="currentSubscaleDescription" :placeholder="activeSubscale.description" autofocus></textarea>
    <button id="describe-subscale-button" @click="describeSubscale()" :disabled="currentSubscaleDescription.trim()==activeSubscale.description.trim()||currentSubscaleDescription.trim().trim()===''">save description</button>
    
    <button class="back-button" @click="editSection=0;setTabActive(2)" @keydown.enter="">&larr; &nbsp; Return to Subscale Edit Overview</button>

  </div>

</div>

<div class="tab-section bg-white" id="tab-add-evaluables" v-if="tabIsActive(2) && editSection==3 && activeSubscaleId!==undefined">

    <p>
      <strong>Add questions to '{{ activeSubscale.name }}'</strong> and <strong>set a weight</strong> for those questions.
      Most of the time, you want to make sure, that the min and max scores for subscales you want to compare are equal. 
    </p>

      <ul class="form-fields">
        <li v-for="field in fields" class="flex-row" :class="{'selected-second-level': fieldIsInSubscale(field)}" >
            <div v-if="field.usable">
            <form v-if="field.usable">
                <p class="field_id">
                    {{ field.field_id }}{{ (field.sub_question !== null) ?  '.' + field.sub_question : ''}}
                </p>

                <p class="strong"> Add this Question to subscale  "{{ activeSubscale.name }}"? </p>
                <span>
                    <input type="radio" :id="['add-to-subscale' + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]" :checked="fieldIsInSubscale(field)" name="add-to-subscale" @change="setFieldInSubscale(field, true);" value="yes"/>
                    <label :for="['add-to-subscale' + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]">Yes</label>
                </span>
                <span>
                    <input type="radio" :id="['remove-from-subscale'  + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]" :checked="!fieldIsInSubscale(field)" name="add-to-subscale"  @change="setFieldInSubscale(field, false);" value="no" />
                    <label :for="['remove-from-subscale'  + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]">No</label>
                </span>

                <p class="strong" >What type does this question have?</p>
                <ul class="question-type-list"><li class="question-type">{{ field.type }}</li></ul>

                <p class="strong">Is the question to be weighted differently than other questions?</p>
                <p><input type="number" v-model="field.weight" step=".01" style="width: 15rem; margin-left:0;"  :id="['question-weight-' + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]" /></p>
                
            
            </form>
            </div>
            <div class="field-preview" :class="{'usable': field.usable, 'unusable': !field.usable,}">
                <div :class="[field.type + '-type']" v-html="field.preview">{{ field.preview }}</div>
            </div>
        </li>
      </ul>

      <button id="describe-subscale-button" @click="saveFieldsInSubscale()" :disabled="!evaluablesChanged">{{ evaluablesChanged ? 'save questions' : 'save questions (nothing new to save)' }}</button>


    <button class="back-button" @click="editSection=0;setTabActive(2)" @keydown.enter="">&larr; &nbsp; Return to Subscale Edit Overview</button>
</div>

<div class="tab-section  bg-white" v-if="tabIsActive(2) && newSubscaleMode && activeSubscaleId==undefined">

    <div class="fit-content">
    

      <input type="text" style="width: 100%;font-size:1.3rem; padding:0.5rem 1rem; border-radius:0;" autofocus v-model="currentSubscaleName" @keypress.enter="document.querySelector('#rename-subscale-button').click()" placeholder="Subscale Name" autofocus/>
      <textarea id="describe-subscale-text" type="text" v-model="currentSubscaleDescription" placeholder="Subscale Description" autofocus></textarea>
    
      <button class="back-button" @click="clickNewSubscaleButton()" @keydown.enter="">{{ (currentSubscaleName !== "") ? `Save and Edit Subscale "${currentSubscaleName}" &nbsp; &rarr;` : "&larr; &nbsp; Return to Subscale Overview" }}</button>
    </div>

</div>


<div class="tab-section  bg-white" v-if="tabIsActive(2) && editSection==4 && activeSubscaleId!==undefined">


        <div class="flex-column" >
            <form v-if="activeSubscaleId != undefined" class="add-subscale-data-form">
                <div class="flex-row">
                    <div>
                        <p class="strong"><label for="data-type">How do you like to input the data?</label></p>

                        <select name="data-type" id="data-type" @change="currentGroupResultType = $event.target.value">
                            <option :selected="true" value="subscale-key-data">Subscale Key Data</option>
                            <option disabled="disabled" value="questions-key-data">Per question key data (Not yet available)</option>
                            <option disabled="disabled" value="subscale-raw-data">Subscale original data (Not yet available)</option>
                            <option disabled="disabled" value="questions-raw-data">Per question original data (Not yet available)</option>
                        </select>
                    </div>

                    <div>
                        <h2>Subscale Key Data</h2>
                        <p>
                        You can enter the statistical data for a <strong>comparison group</strong> as <strong>key data.</strong>
                        This means that instead of the original data you only have to enter key data such as mean value, median and standard deviation.
                        Using key data is sufficient for most visualizations, but some types of visualizations like a point cloud need raw data.
                        If you enter the data <strong>in relation to the subscale,</strong> this means that the data refers to the <strong>sum over all single question-scores</strong> in the subscale.
                        </p>
                    </div>
                </div>

                <hr />


                <div class="flex-row">
                    <div>
                        <p class="strong"><label for="data-type">What is the name of the comparison group for which you want to add results to '{{ activeSubscale.name }}'?</label></p>
                        <input id="data-group-name" type="text" list="groups-list" placeholder="Group Name" v-model="currentGroupName"/>

                        <datalist id="groups-list">
                            <option v-for="group in groups" :value="group">
                        </datalist>
                    </div>

                    <div><!-- Placeholder --></div>

                </div>
                <div v-if=" currentGroupResultType == 'subscale-key-data' ">
                    <div class="flex-row">
                        <div>
                            <p class="strong"><label for="data-type">What is the lowest possibly archivable score on '{{ activeSubscale.name }}'?</label></p>
                            <input id="data-subscale-min" type="number" placeholder="Lowest Archivable Score" v-model.number="currentGroupForm['data']['subscale-key-data']['min']" />
                        </div>

                        <div>
                            <p class="strong"><label for="data-type">What is the highest possibly archivable score on '{{ activeSubscale.name }}'?</label></p>
                            <input id="data-subscale-max" type="number" placeholder="Highest Archivable Score" v-model.number="currentGroupForm['data']['subscale-key-data']['max']"/>
                        </div>

                    </div>

                    <div class="flex-row">
                        <div>
                            <p class="strong"><label for="data-type">What was the lowest archived score for the comparison group on '{{ activeSubscale.name }}'?</label></p>
                            <input id="data-subscale-min-value" type="number" placeholder="Lowest Archived Score" v-model.number="currentGroupForm['data']['subscale-key-data']['min-value']" />
                        </div>

                        <div>
                            <p class="strong"><label for="data-type">What was the highest archived score for the comparison group on '{{ activeSubscale.name }}'?</label></p>
                            <input id="data-subscale-max-value" type="number" placeholder="Heighest Archived Score" v-model.number="currentGroupForm['data']['subscale-key-data']['max-value']" />
                        </div>

                    </div>

                    <div class="flex-row">
                        <div>
                            <p class="strong"><label for="data-type">What is the average score for the comparison group on '{{ activeSubscale.name }}'?</label></p>
                            <input id="data-subscale-average" type="number" placeholder="Average Score" v-model.number="currentGroupForm['data']['subscale-key-data']['average']" />
                        </div>

                        <div>
                            <p class="strong"><label for="data-type">What is the standard deviation for the comparison group's score on '{{ activeSubscale.name }}'? </label></p>
                            <input id="data-subscale-standard-deviation" type="number" placeholder="Standard Deviation" v-model.number="currentGroupForm['data']['subscale-key-data']['standard-deviation']" />
                        </div>

                    </div>
                </div>

                <br /> <br />
                <input type="button" :value="[((currentGroupResult == undefined) ? 'Add Group Result for \'' + currentGroupName + '\' to ' : 'Modify Group Result for ' + currentGroupName + ' in ') + activeSubscale.name]" :disabled="!groupResultFilledOut" @click="addOrEditGroupResult()"  class="secondary" />
                <input type="button" :value="['Delete Group Result for \'' + currentGroupName + '\' in ' + activeSubscale.name]" v-if="!(currentGroupResult == undefined)" @click="deleteGroupResult()"  class="secondary" style="background-color:#DD0000;"/>
            </form>
            <ul v-if="activeSubscaleId == undefined"  class="form-fields" >
                <li style="text-align:center;">
                    <h2 style="margin-top: 10vh">Select a Subscale<h2>
                    <p style="margin-bottom: 10vh">You need to select a subscale in order to add data for comparison groups to that subscale.</p>
                </li>
            </ul>

            <ul v-if="activeSubscaleId != undefined"  class="form-fields">
                <li v-if="activeSubscale.group_results.length === 0" style="text-align:center;">
                    <h2 style="margin-top: 10vh">Add Group to "{{ activeSubscale.name }}"<h2>
                    <p style="margin-bottom: 10vh">Click on the 'Add Group' button to add a new comparison group to the current subscale. After that you can then add statistical data for that group.</p>
                </li>

                <li v-for="group in activeSubscale.group_results" v-if="!group.__deleted__"  :class="{'selected': currentGroupResult != undefined && currentGroupResult.group == group.group, 'clickable': currentGroupResult == undefined || currentGroupResult.group != group.group }" @click="toggleEditSubscale(group.group)">
                    <h2>{{ group.group }}<h2>
                </li>
            </ul>

        </div>

        <input type="button" value="&larr; Return to Subscale Overview" @click="editSection = 0"  class="primary" />
    </div>





</div>