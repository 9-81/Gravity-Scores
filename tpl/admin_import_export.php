<ul class="gs-tabs">
    <li class="gs-tab-item">
        <a class="tab-link" @click.prevent="activateTab(0)" :class="{ active: tabIsActive(0) }">Import</a>
    </li>
    <li class="gs-tab-item">
        <a class="gs-tab-link" @click.prevent="activateTab(1); exportType=''; exportTestId=-1" :class="{ active: tabIsActive(1) }">Export</a>
    </li>
</ul>


<div id="wizard-wrapper" data-nonce="<?= wp_create_nonce('wp_rest') ?>">

    <div class="gs-section flex-column" :class="{'show': tabIsActive(0) }" id="gs-tab-section-0" v-if="tabIsActive(0)">

        <div class="notice notice-error" v-if="importStatus===false">
            <p>There was at least one file-to-import that did not contain valid data.</p>
        </div>

        <div class="notice notice-success" v-if="importStatus===true">
            <p>All files were imported successfully.</p>
        </div>

        <form method="post" enctype="multipart/form-data">
            <p>Select the Gravity Scores export files you would like to import. When you click the import button below, Gravity Scores will import the tests and evaluations.</p>
            <p><input type="file" id="gs-select-import-files" @change="importFiles = $event.target.files" multiple></p>
            <input type="submit" value="Import" name="submit" :disabled="Object.keys(importFiles).length == 0" @click.prevent="importFilesToGravityScores()" />
        </form>
    </div>

    <div class="gs-section flex-column" :class="{'show': tabIsActive(1) }" id="gs-tab-section-1" v-if="tabIsActive(1)">

        <form>
            <p>
                <label for="export-type">What do you want to export?</label>
                <select name="export-type" id="export-type" @change="exportType = $event.target.value; exportTestId = -1;  resetExport()">
                    <option selected="true" disabled="true">-- select --</option>
                    <option value="evaluation">Evaluation</option>
                    <option value="test">Test</option>
                </select>
            </p>
            
            <div v-if="exportType=='test'">
                <p>
                    <label for="export-select-test">Which test do you want to export?</label>
                    <select id="export-select-test" class="selectArtifact" @change="exportTestId = $event.target.value;">
                        <option selected="true" disabled="true">-- select --</option>
                        <option v-for="test in tests" :value="[ test['id'] ]">{{ test['form_title'] }}</li>
                    </select>
                </p>

                <input type="submit" @click.prevent="downloadSelectedTest()" :value="[ (exportTest == '') ? 'Download' :  'Download ' + exportTest.form_title.toLowerCase() + '.json']" :disabled="exportTest==''" />
            </div>

            <div v-if="exportType=='evaluation'">
                <p>
                    <label for="export-select-evaluation">Which evaluation do you want to export?</label>
                    <select id="export-select-evaluation" class="selectArtifact" @change="exportEvaluationId = $event.target.value;">
                        <option selected="true" disabled="true">-- select --</option>
                        <option v-for="evaluation in evaluations" :value="[ evaluation['id'] ]">{{ evaluation['title'] }}</li>
                    </select>
                </p>

                <input type="submit" @click.prevent="downloadSelectedEvaluation()" :value="[ (exportEvaluation == '') ? 'Download' :  'Download ' + exportEvaluation.title.toLowerCase() + '.json']" :disabled="exportEvaluation==''" />
            </div>

        </form>


    </div>

</div>