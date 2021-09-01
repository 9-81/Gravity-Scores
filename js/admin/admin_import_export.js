import Vue from 'vuejs'
import Axios from 'axios'


window.app = new Vue({
    el: '.wrap.import_export',
    data: {
        activeTab: 0,
        importFiles: [],
        importStatus: null,
        exportType: "",
        exportTestId: -1,
        exportEvaluationId: -1,
        api: Axios.create({
            baseURL: localURLs.rest,
            headers: {
                'content-type': 'application/json',
                'X-WP-Nonce': document.querySelector('#wizard-wrapper').getAttribute('data-nonce')
            }
        }),
        evaluations: {},
        tests: {},
    },
    beforeMount() {
        this.api.get('gravityscores/v1/evaluations').then((response) => {
            this.evaluations = response.data.evaluations
        })

        this.api.get('gravityscores/v1/tests').then((response) => {
            this.tests = response.data.tests
        })
    },
    computed: {
        exportTest() {
            if (this.exportTestId == -1)
                return ""

            return this.tests.filter((test) => {
                return test.id == this.exportTestId
            })[0]
        },
        exportEvaluation() {
            if (this.exportEvaluationId == -1)
                return ""

            return this.evaluations.filter((evaluation) => {
                return evaluation.id == this.exportEvaluationId
            })[0]
        }
    },
    methods: {
        tabIsActive: function (tabId) {
            return tabId == this.activeTab
        },
        activateTab: function (tabId) {
            this.activeTab = tabId

            this.$forceUpdate
        },
        download: function (filename, text) {
            var element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
            element.setAttribute('download', filename);

            element.style.display = 'none';
            document.body.appendChild(element);

            element.click();

            document.body.removeChild(element);
        },
        cleanExport: function (exportData) {
            if (Object.keys(exportData).includes('tests')) {
                console.log(exportData.tests)
                for (let testIndex in exportData.tests) {
                    delete exportData.tests[testIndex].id
                    delete exportData.tests[testIndex].form_id

                    for (let subscaleIndex in exportData.tests[testIndex].subscales) {
                        delete exportData.tests[testIndex].subscales[subscaleIndex].id
                        delete exportData.tests[testIndex].subscales[subscaleIndex].test_id

                        for (let resultIndex in exportData.tests[testIndex].subscales[subscaleIndex].group_results) {
                            delete exportData.tests[testIndex].subscales[subscaleIndex].group_results[resultIndex].id
                        }

                        // ToDo: Clean Evaluables
                    }
                }
            }
            return exportData
        },
        downloadSelectedTest: function () {
            this.api.get('gravityscores/v1/test/' + this.exportTestId).then((response) => {
                this.download(this.exportTest.form_title + '.json', JSON.stringify(this.cleanExport(response.data)))
            })
        },
        downloadSelectedEvaluation: function () {
            this.api.get('gravityscores/v1/evaluation/' + this.exportEvaluationId).then((response) => {
                this.download(this.exportEvaluation.title + '.json', JSON.stringify(this.cleanExport(response.data)))
            })
        },
        importFilesToGravityScores: function () {
            console.log('test')
            let reader = {}
            for (const i of Object.keys(this.importFiles)) {
                reader = new FileReader();
                reader.onload = (e) => {
                    const file = e.target.result;
                    try {
                        const file_contents = JSON.parse(file.split(/\r\n|\n/).join('\n'))
                        this.api.post('gravityscores/v1/import/', file_contents).then((response) => {
                            this.importStatus = (this.importStatus === null) ? response.data : this.importStatus && response.data
                            console.log(response)
                        });
                    } catch (error) {
                        this.importStatus = false
                    }

                };
                reader.readAsText(this.importFiles[i])
            }
            document.querySelector('#gs-select-import-files').value = ''
        },
        resetExport(){
            document.querySelectorAll('select.selectArtifact').forEach(element => {
                element.selectedIndex = 0;
            })
        }
    }
})