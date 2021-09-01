<ul class="wizard-tabs">
    <li class="wizard-tab-item">
        <a class="wizard-tab-link" @click.prevent="wizardStep(0)" :class="{ active: wizardActive(0), disabled: wizardDisabled(0) }">Select Form</a>
    </li>
    <li class="wizard-tab-item">
        <a class="wizard-tab-link" @click.prevent="wizardStep(1)" :class="{ active: wizardActive(1), disabled: wizardDisabled(1) }">Create Subscales</a>
    </li>
    <li class="wizard-tab-item">
        <a class="wizard-tab-link" @click.prevent="wizardStep(2)" :class="{ active: wizardActive(2), disabled: wizardDisabled(2) }">Add Questions</a>
    </li>
    <li class="wizard-tab-item">
        <a class="wizard-tab-link" @click.prevent="wizardStep(3)" :class="{ active: wizardActive(3), disabled: wizardDisabled(3) }">Add Data</a>
    </li>
</ul>


<div id="wizard-wrapper" data-nonce="<?= wp_create_nonce('wp_rest') ?>">

    <!-- WIZARD STEP 0: NO FORMS -->
    <div class="wizard-section flex-column" :class="{'show': wizardActive(0) }" id="wizard-section-0" v-if="wizardActive(0) && forms.length == 0">
    
    <p style="font-size: 1.3rem; margin:1rem 1rem 0 1rem;">To create a <strong style="padding:0;">new test.</strong> You need to <strong style="padding:0;">create a form in Gravity Forms first.</strong></p>
    <ul class="new_form_button">
        <li style="margin:0.5rem;"><a href="admin.php?page=gf_new_form">Add new Form</a></li>
    </ul>

    </div>


    <!-- WIZARD STEP 0 -->
    <div class="wizard-section flex-column" :class="{'show': wizardActive(0) }" id="wizard-section-0" v-if="wizardActive(0) && forms.length > 0">

        <div>
            <p class="section-description">Please choose a Gravity Forms form and proceed.</p>    
            <input type="text" id="search-form" placeholder="Search forms" v-model="search" autofocus />
        </div>

        <div class="flex-row">

            <ul id="list-select-form">
                <li v-for="(form, formIndex) in searchForms" tabindex="0" @keydown.space.prevent="activate(form.id, $event);" @keydown.enter.prevent="activate(form.id, $event);finishSelectForm()" @click="activate(form.id, $event)" class="list-select-item clickable" :class="{'selected': form.id == active, 'previous': form.id == test.form_id}">
                    <span class="form-title">{{  form.title }}</span>
                    <span class="form-id">{{ form.id }}</span>
                </li>
            </ul>

            <div class="form-preview">
                <div v-if="typeof currentElement !== 'undefined'">
                    <span class="form-preview-id">{{ currentElement.id }}</span>    
                    <h1>{{ currentElement.title }}</h1>
                    <hr  />
                    <p v-if="currentElement.description.trim().length != 0">{{ currentElement.description }}</p>
                </div>
                
                <div v-if="typeof currentElement === 'undefined'">
                    <h1>Further Information</h1>
                    <hr />
                    <p>After a form is selected, more information about that form is shown here.</p>
                </div>    
            </div>

        </div>

        <input type="button" value="Select & Finish Step" :disabled="active === -1" @click="finishSelectForm()"  class="primary" />
    </div>

    <!-- WIZARD STEP 1 -->
    <div class="wizard-section flex-column" :class="{'show': wizardActive(1) }" id="wizard-section-1" v-if="wizardActive(1)">


        <div class="flex-row">
            <form class="form-create-subscale" @keydown.enter.prevent="submitSubscale($event)">
                <h1>{{ (active == -1) ? 'Add Subscale' : 'Edit Subscale' }}</h1>
                <p v-if="active == -1">
                    To add a subscale to '<strong>{{ currentForm.title}}</strong>' enter a name and a description for that subscale and click on `Add Subscale`. 
                </p>
                <p v-if="active != -1">
                    You are editing the subscale '<strong>{{ currentElement.name }}</strong>' on '<strong>{{ currentForm.title}}</strong>'.
                    Press the `Save Subscale` button to modify the subscale.
                </p>
                
                <hr />
            
                <p class="input-label"><label for="subscale-name-text">Name</label></p>

                <input id="subscale-name-text" type="text" placeholder="'Artistic' is an example for a test of intrest" autofocus />
                
                <p class="input-label"><label for="subscale-description-textarea">Description</label></p>
                <textarea id="subscale-description-textarea" placeholder="Write a description of your subscale here ..."></textarea>
            

                <input id="add-subscale-button" class="secondary" type="button" :value="[ (active == -1) ? 'Add Subscale' : 'Save Subscale' ]" @click="submitSubscale($event)" />
            </form>
            <div class="list-created-subscales-wrapper">

                <div v-if="test.subscales.length == 0" class="list-created-subscales no-subscales">
                    <h2>Currently no subscale available.</h2>
                    <p>Please click the `Add Subscale` button to add one.</p>    
                </div>
                <ul class="list-created-subscales" v-if="test.subscales.length >= 0">
                    <li v-for="subscale in test.subscales" class="list-select-item clickable" tabindex="0" :class="[ subscale.state, (subscale.tempId == active) ? 'selected' : '' ]"  @keydown.enter.prevent="toggleSubscaleEditMode(subscale);focusInputSubscaleName()" @keydown.space.prevent="toggleSubscaleEditMode(subscale);" @click="toggleSubscaleEditMode(subscale)" >
                        <span class="subscale-name">{{subscale.name}}</span>
                    </li>
                </ul>
            </div>
            
        </div>
        <input type="button" value="Finish Step" :disabled="test.subscales.length === 0" @click="wizardStep()"  class="primary" />
    </div>

    <!-- WIZARD STEP 2 -->
    <div class="wizard-section flex-column" :class="{'show': wizardActive(2) }" id="wizard-section-2" v-if="wizardActive(2)">
        
        <div>
            <p class="section-description">Fist select the subscale, then add questions from your Gravity Forms form as Questions to it. Go to the next step after you added the corresponding questions to all of your subscales.</p>
            <ul class="list-available-subscale-pages">
                <li v-for="subscale in test.subscales" class="flex-row list-select-item clickable" tabindex="0" :class="{'selected': currentElement != undefined && currentElement.tempId === subscale.tempId }" @click="selectSubscalePage(subscale)" @keydown.enter.prevent="selectSubscalePage(subscale)" @keydown.space.prevent="selectSubscalePage(subscale)" >
                    {{ subscale.name }}
                </li>
            </ul>

        </div>

        <div class="flex-column" >
        
            <ul class="form-fields">
                <li v-if="currentElement == undefined" style="text-align:center;">
                    <h2 style="margin-top: 10vh">Select a Subscale<h2>
                    <p style="margin-bottom: 10vh">You need to select a subscale in order to add questions to that subscale.</p>
                </li>

                <li v-for="field in fields" class="flex-row" v-if="currentElement != undefined" :class="{'selected-second-level': field.subscales.includes(currentElement.tempId)}">
                    <div v-if="field.usable">
                    <form v-if="field.usable">
                        <p class="field_id">
                            {{ field.field_id }}{{ (field.sub_question !== null) ?  '.' + field.sub_question : ''}}
                        </p>

                        <p class="strong"> Add this Question to subscale  "{{ currentElement.name }}"? </p>
                        <span>
                            <input type="radio" :id="['add-to-subscale' + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]" name="add-to-subscale" @change="toggleFieldInSubscale($event, field, currentElement)" value="yes"/>
                            <label :for="['add-to-subscale' + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]">Yes</label>
                        </span>
                        <span>
                            <input type="radio" :id="['remove-from-subscale'  + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]" checked="checked" name="add-to-subscale"  @change="toggleFieldInSubscale($event, field, currentElement)" value="no" />
                            <label :for="['remove-from-subscale'  + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]">No</label>
                        </span>

                        <p class="strong" >What type does this question have?</p>
                        <ul class="question-type-list"><li class="question-type">{{ field.type }}</li></ul>

                        <p class="strong">Is the question to be weighted differently than other questions?</p>
                        <p><input type="number" value="1" step=".01" style="width: 15rem; margin-left:0;" @change="changeWeight($event, field, currentElement)" :id="['question-weight-' + field.field_id + ((field.sub_question == null) ? '' : '_' + field.sub_question)]" /></p>
                        
                    
                    </form>
                    </div>
                    <div class="field-preview" :class="{'usable': field.usable, 'unusable': !field.usable,}">
                        <div :class="[field.type + '-type']" v-html="field.preview">{{ field.preview }}</div>
                    </div>
                </li>
            </ul>
        </div>

        <input type="button" :value="['Next Subscale (' + nextSubscaleName() + ')'  ]" :disabled="test.subscales.length === 0" @click="nextSubscale()"  class="secondary" />
        <input type="button" value="Finish Step" :disabled="test.subscales.length === 0" @click="wizardStep()"  class="primary" />
    </div>

    <!-- WIZARD STEP 3 -->
    <div class="wizard-section" :class="{'show': wizardActive(3) }" id="wizard-section-3" v-if="wizardActive(3)">
        <div>
            <p class="section-description">Fist select the subscale, then use the 'Add Group' Button to the add data for a new comparison group on that subscale. </p>
            <ul class="list-available-subscale-pages">
                <li v-for="subscale in test.subscales" class="flex-row list-select-item clickable" tabindex="0" :class="{'selected': currentElement != undefined && currentElement.tempId === subscale.tempId }" @click="selectSubscalePage(subscale)" @keydown.enter.prevent="selectSubscalePage(subscale)" @keydown.space.prevent="selectSubscalePage(subscale)" >
                    {{ subscale.name }}
                </li>
            </ul>
        </div>

        <div class="flex-column" >
            <form v-if="currentElement != undefined" class="add-subscale-data-form">
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
                        <p class="strong"><label for="data-type">What is the name of the comparison group for which you want to add results to '{{ currentElement.name }}'?</label></p>
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
                            <p class="strong"><label for="data-type">What is the lowest possibly archivable score on '{{ currentElement.name }}'?</label></p>
                            <input id="data-subscale-min" type="number" placeholder="Lowest Archivable Score" v-model.number="currentGroupForm['data']['subscale-key-data']['min']" />
                        </div>

                        <div>
                            <p class="strong"><label for="data-type">What is the highest possibly archivable score on '{{ currentElement.name }}'?</label></p>
                            <input id="data-subscale-max" type="number" placeholder="Highest Archivable Score" v-model.number="currentGroupForm['data']['subscale-key-data']['max']"/>
                        </div>

                    </div>

                    <div class="flex-row">
                        <div>
                            <p class="strong"><label for="data-type">What was the lowest archived score for the comparison group on '{{ currentElement.name }}'?</label></p>
                            <input id="data-subscale-min-value" type="number" placeholder="Lowest Archived Score" v-model.number="currentGroupForm['data']['subscale-key-data']['min-value']" />
                        </div>

                        <div>
                            <p class="strong"><label for="data-type">What was the highest archived score for the comparison group on '{{ currentElement.name }}'?</label></p>
                            <input id="data-subscale-max-value" type="number" placeholder="Heighest Archived Score" v-model.number="currentGroupForm['data']['subscale-key-data']['max-value']" />
                        </div>

                    </div>

                    <div class="flex-row">
                        <div>
                            <p class="strong"><label for="data-type">What is the average score for the comparison group on '{{ currentElement.name }}'?</label></p>
                            <input id="data-subscale-average" type="number" placeholder="Average Score" v-model.number="currentGroupForm['data']['subscale-key-data']['average']" />
                        </div>

                        <div>
                            <p class="strong"><label for="data-type">What is the standard deviation for the comparison group's score on '{{ currentElement.name }}'? </label></p>
                            <input id="data-subscale-standard-deviation" type="number" placeholder="Standard Deviation" v-model.number="currentGroupForm['data']['subscale-key-data']['standard-deviation']" />
                        </div>

                    </div>
                </div>

                <br /> <br />
                <input type="button" :value="[((currentGroupResult == undefined) ? 'Add Group Result for ' + currentGroupName + ' to ' : 'Modify Group Result for ' + currentGroupName + ' in ') + currentElement.name]" :disabled="!groupResultFilledOut" @click="addOrEditGroupResult()"  class="secondary" />
            </form>
            <ul v-if="currentElement == undefined"  class="form-fields" >
                <li style="text-align:center;">
                    <h2 style="margin-top: 10vh">Select a Subscale<h2>
                    <p style="margin-bottom: 10vh">You need to select a subscale in order to add data for comparison groups to that subscale.</p>
                </li>
            </ul>

            <ul v-if="currentElement != undefined"  class="form-fields">
                <li v-if="currentElement.group_results.length === 0" style="text-align:center;">
                    <h2 style="margin-top: 10vh">Add Group to "{{ currentElement.name }}"<h2>
                    <p style="margin-bottom: 10vh">Click on the 'Add Group' button to add a new comparison group to the current subscale. After that you can then add statistical data for that group.</p>
                </li>

                <li v-if="currentElement.group_results.length !== 0" v-for="group in currentElement.group_results" :class="{'selected': currentGroupResult != undefined && currentGroupResult.group == group.group, 'clickable': currentGroupResult == undefined || currentGroupResult.group != group.group }" @click="toggleEditSubscale(group.group)">
                    <h2>{{ group.group }}<h2>
                </li>
            </ul>

        </div>

        <input type="button" value="Submit and Save" :disabled="test.subscales.length === 0" @click="submit()"  class="primary" />
    </div>

</div>