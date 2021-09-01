<?php
    global $wpdb;
    $table = $wpdb->prefix . 'gs_tests';
    $num_tests = $wpdb->get_var("SELECT COUNT(*) FROM $table");
?>

<?php  if ($num_tests > 0): ?>
<ul class="wizard-tabs">
    <li class="wizard-tab-item">
        <a class="wizard-tab-link" @click.prevent="wizardSelectStep(0)" :class="{ active: wizardStep==0, disabled: wizardEnabled < 0 }">Choose Title</a>
    </li>
    <li class="wizard-tab-item">
        <a class="wizard-tab-link" @click.prevent="wizardSelectStep(1)" :class="{ active: wizardStep==1, disabled: wizardEnabled < 1 }">Select Visualization</a>
    </li>
    <li class="wizard-tab-item">
        <a class="wizard-tab-link" @click.prevent="wizardSelectStep(2)" :class="{ active: wizardStep==2, disabled: wizardEnabled < 2 }">Select Tests</a>
    </li>
    <li class="wizard-tab-item">
        <a class="wizard-tab-link" @click.prevent="wizardSelectStep(3)" :class="{ active: wizardStep==3, disabled: wizardEnabled < 3 }">Select Subscales</a>
    </li>
</ul>


<div id="wizard-wrapper">

    <!-- WIZARD STEP 0 -->
    <div class="wizard-section flex-column" id="wizard-section-3" v-if="wizardStep==0">
        
        <div class="chooseEvaluationNameWrapper">
        <p>Please enter a name for the evaluation.</p>
        <input type="text" v-model="evaluationName" id="evaluation_name" placeholder="Evaluation Name" :autofocus="'autofocus'" @keypress.enter.prevent="wizardNextStep()" />
        <input style="font-size:1.3rem;" type="button" class="primary" @click.prevent="wizardNextStep()" :class="{'disabled': evaluationName === ''}" :disabled="evaluationName === ''" value="Confirm Name & Finish Step" />
        </div>
    </div>

    <!-- WIZARD STEP 1 -->
    <div class="wizard-section flex-column" id="wizard-section-0" v-if="wizardStep==1">
        <p>Gravity Scores supports multiple types of visualizations. Please select one and proceed. </p>

        <ul class="visualizations">
            <li v-for="visualization in visualizations" tabindex="0" @focus="selectVisualization(visualization)"  class="selectable" :class="{'selected': selectedVisualization==visualization}">
                {{ visualization.name }}
            </li>
        </ul>

        <input type="button" class="primary" @click.prevent="wizardNextStep()" :class="{'disabled': selectedVisualization === undefined}" :disabled="selectedVisualization === undefined" value="Select & Finish Step" />

    </div>

    <!-- WIZARD STEP 2 -->
    <div class="wizard-section flex-column" id="wizard-section-1" v-if="wizardStep==2">
        <p v-if="selectedVisualization.max_subscales == 1">Select a test to procceed.</p>
        <p v-if="selectedVisualization.max_subscales > 1">Select at least one test to proceed. Select multiple tests, if you want visualize a comparison between multiple subscales.</p>
    
        <ul class="visualizations" v-if="selectedVisualization.max_subscales == 1" >
            <li v-for="test in tests" tabindex="0" @focus="selectedTests.push(test)"  class="selectable" :class="{'selected': selectedTests.includes(test)}">
                <span class="form_title">{{ test.form_title }}</span>
                <span class="test_id">{{ test.id }}</span>
            </li>
        </ul>

        <ul class="visualizations" v-if="selectedVisualization.max_subscales > 1" >
            <li v-for="test in tests" tabindex="0" class="selectable" :class="{'selected': selectedTests.includes(test)}" @click="toggleTest(test)" @keypress.enter="toggleTest(test)" @keypress.space="toggleTest(test)" >
                <input type="checkbox" v-model="selectedTests" :value="test" >
                <span class="form_title">{{ test.form_title }}</span>
                <span class="test_id">{{ test.id }}</span>
            </li>
        </ul>

        <input type="button" class="primary" @click.prevent="wizardNextStep()" :class="{'disabled': selectedTests.length === 0}" :disabled="selectedTests.length === 0" value="Select & Finish Step" />

    </div>

    <!-- WIZARD STEP 3 -->
    <div class="wizard-section flex-column" id="wizard-section-2" v-if="wizardStep==3">
        <p v-if="selectedVisualization.max_subscales == 1">Select the subscale of {{ tests[0].form_title }} and click submit to create the evaluation.</p>
        <p v-if="selectedVisualization.max_subscales > 1">Select {{ selectedVisualization.min_subscales }} -  {{ selectedVisualization.max_subscales }} subscales and click submit to save the evaluation.</p>

        <div class="subscale_select_test_container" v-for="test in selectedTests">
            <h2>{{ test.form_title }}</h2>
            <ul class="subscales">
                <li v-for="subscale in test.subscales"><input type="checkbox" v-model="subscale.added" :id=" 'subscale' + subscale.id" />
                <label :for=" 'subscale' + subscale.id">{{ subscale.name }}</label></li>
            </ul>
        </div>

        <input type="button" class="primary" @click.prevent="submit()" :class="{'disabled': selectedVisualization === undefined}" :disabled="selectedVisualization === undefined" value="Finish & Save Evaluation" />

    </div>



</div>
<?php else: ?>
    <div style="margin:1rem;font-size: 1.3rem;">
    <p style="font-size: 1.3rem;">To create a <strong>new evaluation.</strong> You need to <strong>create a test first.</strong></p>
    <ul class="new_test_button">
        <li style="margin:0.5rem;"><a href="admin.php?page=gravityscores_test">Add new Test</a></li>
    </ul>
</div>
<?php endif; ?>