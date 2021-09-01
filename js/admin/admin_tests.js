import Vue from 'vuejs'
import Axios from 'axios'
import ListTable from 'vue-wp-list-table';
import {
    dragDisable
} from 'd3';
//import 'vue-wp-list-table/dist/vue-wp-list-table.css';

document.addEventListener('DOMContentLoaded', function() {

    window.app = new Vue({
        el: '.wrap.tests',
        components: {
            ListTable
        },
        data: {
            document: document,
            api: Axios.create({
                baseURL: localURLs.rest,
                headers: {
                    'content-type': 'application/json',
                    'X-WP-Nonce': nonce
                },
            }),
            tests: [],
            edit: undefined,
            editMode: false,
            saved: false,
            editSection: 0,
            newSubscaleMode: false,
            editSubscaleMode: false,
            activeSubscaleId: undefined,
            currentSubscaleName: "",
            currentSubscaleDescription: "",
            currentGroupName: "",
            currentGroupResultType: "subscale-key-data",
            fields: [],
            groups: [],
            currentGroupForm: {
                data: {
                    'subscale-key-data': {
                        'min': 0,
                        'max': 0,
                        'min-value': 0,
                        'max-value': 0,
                        'average': 0,
                        'standard-deviation': 0
                    }
                }
            },
        },
        beforeMount() {

            this.api.get('gravityscores/v1/tests').then((response) => {

                if (Object.keys(response.data).includes('tests')) {
                    Vue.set(this, 'tests', response.data.tests)
                } else {
                    Vue.set(this, 'tests', [])
                }

            });

            this.api.get('gravityscores/v1/groups').then((response) => {
                this.groups = response.data.map(function(group) {
                    return group.name;
                })
            });

        },
        computed: {

            groupResultFilledOut: function() {

                let currentGroupForm = this.currentGroupForm
                let result = false;

                if (this.currentGroupName.trim() == '') {
                    return result
                }

                Object.keys(currentGroupForm['data']['subscale-key-data']).forEach(function(key) {
                    if (currentGroupForm['data']['subscale-key-data'][key] != 0)
                        result = true
                })

                return result

            },
            currentGroupResult: function() {

                let result = this.activeSubscale.group_results.filter((group) => {
                    return group.group == this.currentGroupName
                })

                if (result.length >= 1) {
                    return result[0]
                }
                return undefined
            },


            editSubscaleTabName: function() {
                if (this.tabIsAvailable(2)) {
                    if (this.newSubscaleMode && this.activeSubscaleId === undefined) {
                        return "New Subscale"
                    }
                    return `Edit "${this.activeSubscale.name}" on ${this.edit.form_title}`
                } else {
                    return 'Edit Subscale'
                }
            },
            edit_subscales: function() {
                if (this.edit == undefined) {
                    return []
                }
                return this.edit.subscales
            },
            activeSubscale: function() {

                if (this.activeSubscaleId !== undefined) {
                    return this.edit.subscales.filter(subscale => {
                        return subscale.id == this.activeSubscaleId
                    })[0]
                } else {
                    return {
                        id: -1,
                        test_id: -1,
                        name: "",
                        description: "",
                        evaluables: [],
                        evaluables_length: 0,
                        group_results: [],
                        group_results_length: 0

                    }
                }
            },
            evaluablesChanged: function() {

                let changed = false


                for (let evaluable of this.activeSubscale.evaluables) {

                    // If the state weather the evaluable is included has changed
                    if (changed || (evaluable.initialInSubscale !== evaluable.containedInSubscale)) {
                        console.log('state changed')
                        changed = true
                        break;
                    }

                    // If the evaluable is included and its weight changed
                    if (evaluable.containedInSubscale) {
                        for (let field of this.fields) {

                            if (field.field_id == evaluable.field_id && field.sub_question == evaluable.sub_question) {

                                changed = field.weight != evaluable.weight
                                if (changed) {
                                    console.log('weight changed')
                                    break;
                                }

                            }
                        }
                    }

                }

                return changed
            }
        },
        methods: {
            log: function(logMessage) {
                console.log(logMessage)
                return logMessage
            },
            clickNewSubscaleButton: function() {
                if (this.currentSubscaleName == "") {
                    this.setTabActive(1)
                } else {
                    let newSubscale = {
                        test_id: this.edit.id,
                        name: this.currentSubscaleName,
                        description: this.currentSubscaleDescription
                    }
                    this.api.post('gravityscores/v1/subscale/', newSubscale).then(response => {
                        response.data['evaluables_length'] = 0
                        response.data['group_results_length'] = 0
                        Vue.set(this.edit.subscales, this.edit.subscales.length, response.data)

                        this.activeSubscaleId = response.data['id']
                        this.editSubscaleMode = true;
                    })
                }
            },
            all_tests_table_actions: function(action, test) {


                if (action == 'edit') {
                    this.api.get('gravityscores/v1/test/' + test.id).then(response => {
                        this.edit = response.data.tests[0]
                        if (Object.keys(this.edit).includes('subscales')) {
                            this.edit.subscales.forEach(subscale => {
                                subscale.evaluables_length = subscale.evaluables.length
                                subscale.group_results_length = subscale.group_results.length
                                subscale.updated = false
                            })

                        }

                    })

                    this.api.get('gravityscores/v1/fields/' + test.form_id).then((response) => {

                        let fields = response.data.map(field => {
                            if (field.usable) {
                                Vue.set(field, 'weight', 1)
                            }
                            return field
                        })

                        Vue.set(this, 'fields', fields)

                    });

                    this.editMode = true
                    this.editSection = 0
                }

                if (action == 'delete') {

                    this.api.delete('gravityscores/v1/test/' + test.id).then((response) => {
                        this.tests = this.tests.filter(element => {
                            return element.id != test.id
                        })

                    }).catch((error) => {
                        console.log(error)
                    })


                }


            },
            subscales_table_actions: function(action, subscale) {
                if (action == 'edit') {
                    this.editSection = 0
                    this.activeSubscaleId = subscale.id
                    this.currentSubscaleName = this.activeSubscale.name
                    this.currentSubscaleDescription = this.activeSubscale.description
                    this.editSubscaleMode = true
                    this.newSubscaleMode = false
                    subscale.evaluables.forEach(evaluable => {
                        Vue.set(evaluable, 'initialInSubscale', true)
                        Vue.set(evaluable, 'containedInSubscale', true)
                    })

                    subscale.group_results.forEach(group_result => {
                        Vue.set(group_result, '__inserted__', false)
                        Vue.set(group_result, '__updated__', false)
                        Vue.set(group_result, '__deleted__', false)
                    })

                    this.fields.forEach(field => {
                        let filtered_evaluables = []

                        if (this.fieldIsInSubscale(field)) {
                            filtered_evaluables = subscale.evaluables.filter(evaluable => {
                                return evaluable.field_id == field.field_id
                            })

                            if (Array.from(filtered_evaluables).length > 1) {
                                filtered_evaluables = filtered_evaluables.filter(evaluable => {
                                    return evaluable.sub_question == field.sub_question
                                })
                            }

                            if (Array.from(filtered_evaluables).length == 1) {
                                field.weight = filtered_evaluables[0].weight
                            }
                        }
                    })


                }

                if (action == 'delete') {
                    subscale.__delete__ = true

                    if (subscale.id == this.activeSubscaleId) {
                        this.activeSubscaleId = undefined
                    }

                    this.submitSubcale().then(() => {
                        this.edit.subscales = this.edit.subscales.filter(element => {
                            return element.id != subscale.id
                        })
                    })
                }

            },
            onBulkAction: function(action, ids) {

                if (this.tabIsActive(0)) {

                    let tests = ids.map(test_id => {
                        return this.tests.filter(test => {
                            return test.id == test_id
                        })[0]
                    })

                    tests.forEach(test => {
                        this.all_tests_table_actions(action, test)
                    })

                }
                if (this.tabIsActive(1)) {

                    let subscales = ids.map(subscale_id => {
                        return this.edit.subscales.filter(subscale => {
                            return subscale.id == test_id
                        })[0]
                    })

                    subscales.forEach(test => {
                        this.subscales_table_actions(action, test)
                    })
                }



            },
            tabIsActive: function(tabNumber) {

                if (tabNumber == 2) {
                    return this.editMode && this.editSubscaleMode
                } else if (tabNumber == 1) {
                    return this.editMode && !this.editSubscaleMode
                } else {
                    return !this.editMode
                }
            },
            tabIsAvailable: function(tabNumber) {
                if (tabNumber == 2) {
                    return this.edit !== undefined && (this.activeSubscaleId !== undefined || this.newSubscaleMode)
                } else if (tabNumber == 1) {
                    return this.edit !== undefined
                } else {
                    return true;
                }
            },
            fieldIsInSubscale: function(field) {

                let result = false

                this.activeSubscale.evaluables.forEach(evaluable => {
                    if (evaluable.field_id == field.field_id) {
                        result = result || ((evaluable.sub_question == field.sub_question) && evaluable.containedInSubscale)
                    }
                })

                return result
            },
            getFieldWeight: function(field) {

                let weight = 1
                if (this.fieldIsInSubscale(field)) {

                    this.activeSubscale.evaluables.forEach(evaluable => {
                        if (evaluable.field_id == field.field_id && evaluable.sub_question == field.sub_question) {
                            weight = evaluable.weight
                        }
                    })

                }
                return weight

            },
            setFieldInSubscale: function(field, add = false) {

                let finished = false

                this.activeSubscale.evaluables.forEach(evaluable => {
                    if (evaluable.field_id == field.field_id && evaluable.sub_question == field.sub_question) {
                        Vue.set(evaluable, 'containedInSubscale', add)
                        finished = true
                        return
                    }
                })

                if (!finished && add) {

                    Vue.set(this.activeSubscale.evaluables, this.activeSubscale.evaluables.length, {
                        containedInSubscale: true,
                        initialInSubscale: false,
                        field_id: field.field_id,
                        sub_question: field.sub_question,
                        type: field.type,
                        weight: field.weight
                    })

                }

                this.$forceUpdate()

            },
            saveFieldsInSubscale: async function() {

                this.activeSubscale.evaluables.forEach((evaluable, index) => {

                    // Ignore new evaluables that were removed
                    if (!evaluable.initialInSubscale && !evaluable.containedInSubscale) {
                        this.activeSubscale.evaluables.splice(index, 1)
                        return
                    }

                    // set default values
                    evaluable.__deleted__ = false
                    evaluable.__updated__ = (evaluable.__updated__ == true) ? true : false
                    evaluable.__inserted__ = false

                    // Delete old evaluables which were removed
                    if (evaluable.initialInSubscale && !evaluable.containedInSubscale) {
                        evaluable.__deleted__ = true
                        return
                    }


                    // Add new evaluables
                    if (!evaluable.initialInSubscale && evaluable.containedInSubscale) {
                        evaluable.__inserted__ = true
                    }

                    // Update if the weight has updated
                    if (!evaluable.__deleted__) {
                        for (let field of this.fields) {
                            if (evaluable.field_id == field.field_id && evaluable.sub_question == field.sub_question) {
                                console.log(field.weight)
                                if (evaluable.weight != field.weight) {
                                    Vue.set(evaluable, '__updated__', !evaluable.__inserted__)
                                    Vue.set(evaluable, 'weight', field.weight)
                                }
                            }
                        }
                    }


                })

                this.activeSubscale.evaluables_length = this.activeSubscale.evaluables.length

                await this.submitSubcale()

                this.activeSubscale.evaluables.forEach((evaluable, index) => {
                    if (evaluable.__deleted__) {
                        this.activeSubscale.evaluables.splice(index, 1)
                    } else {
                        Vue.set(evaluable, 'initialInSubscale', true)
                    }
                })

            },
            setTabActive: function(tabNumber) {

                if (tabNumber == 2) {
                    this.editSubscaleMode = (this.activeSubscaleId !== undefined || this.newSubscaleMode)
                    this.editMode = this.edit !== undefined
                } else if (tabNumber == 1) {
                    this.editMode = this.edit !== undefined
                    this.editSubscaleMode = false
                } else if (tabNumber == 0) {
                    this.editMode = false
                    this.editSubscaleMode = false
                }

            },
            renameSubscale: async function() {

                if (this.currentSubscaleName.trim() == "")
                    return

                if (this.tabIsActive(2)) {
                    let subscale = this.edit.subscales.filter(subscale => {
                        return subscale.id == this.activeSubscaleId
                    })[0]

                    subscale.updated = true
                    subscale.name = this.currentSubscaleName
                    await this.submitSubcale()
                }

            },
            describeSubscale: async function() {
                if (this.tabIsActive(2)) {
                    let subscale = this.edit.subscales.filter(subscale => {
                        return subscale.id == this.activeSubscaleId
                    })[0]

                    subscale.updated = true
                    subscale.description = this.currentSubscaleDescription
                    await this.submitSubcale()
                }
            },
            submitSubcale: async function() {

                let data = {
                    tests: [this.edit],
                    options: {
                        import_tests_only: true,
                        update: true
                    }
                }
                console.log(data)
                let response = await this.api.put('gravityscores/v1/import/', data)

                console.log(response)

                this.edit.subscales.forEach(subscale => {
                    subscale.updated = false
                })

            },
            /*            saveSubscaleGroupResults: async function() {

                            await this.submitSubcale()

                            for (let index in this.activeSubscale.group_results) {

                                let result = this.activeSubscale.group_results[index]

                                if (result.__deleted__) {
                                    this.activeSubscale.group_results.splice(index, 1)
                                } else {
                                    result.__inserted__ = false
                                    result.__updated__ = false
                                }

                            }

                            this.editSection = 0

                        },*/
            addOrEditGroupResult: function() {

                if (!this.groups.includes(this.currentGroupName)) {
                    this.groups.push(this.currentGroupName)
                }

                let keyData = this.currentGroupForm['data']['subscale-key-data']
                let group_result

                if (this.currentGroupResult == undefined) {

                    group_result = {
                        '__inserted__': true,
                        '__updated__': false,
                        '__deleted__': false,
                        'subscale_id': this.activeSubscale.id,
                        'group': this.currentGroupName,
                        'data': {
                            'subscale-key-data': Object.assign({}, keyData)
                        }
                    }

                } else {

                    group_result = {
                        '__inserted__': this.currentGroupResult.__inserted__,
                        '__updated__': !this.currentGroupResult.__inserted__,
                        '__deleted__': false,
                        'subscale_id': this.activeSubscale.id,
                        'group': this.currentGroupName,
                        'id': this.currentGroupResult.id,
                        'data': {
                            'subscale-key-data': Object.assign({}, keyData)
                        }
                    }

                }

                this.api.put('gravityscores/v1/group_result/', group_result).then(response => {

                    if (group_result.__inserted__) {
                        group_result.id = response.data
                        group_result.__inserted__ = false
                        this.activeSubscale.group_results.push(group_result)
                    }

                    if (group_result.__updated__) {
                        let index = this.activeSubscale.group_results.indexOf(this.currentGroupResult)
                        group_result.__updated__ = false
                        this.activeSubscale.group_results.splice(index, 1, group_result)
                    }

                })

                this.currentGroupName = ''

                Object.keys(keyData).forEach((key) => {
                    keyData[key] = 0
                })

                this.$forceUpdate
            },
            toggleEditSubscale: function(groupName) {
                if (this.currentGroupName == groupName) {
                    this.currentGroupName = ''

                    for (let key in this.currentGroupForm['data']['subscale-key-data']) {
                        this.currentGroupForm.data['subscale-key-data'][key] = 0
                    }

                } else {
                    this.currentGroupName = groupName
                    Vue.set(this.currentGroupForm.data, 'subscale-key-data', Object.assign({}, this.currentGroupResult['data']['subscale-key-data']))
                }

                this.$forceUpdate
            },
            deleteGroupResult: function() {

                let index = this.activeSubscale.group_results.indexOf(this.currentGroupResult)
                let group_result_id = this.activeSubscale.group_results[index].id

                this.api.delete('gravityscores/v1/group_result/' + group_result_id).then(response => {
                    console.log(response)
                    this.activeSubscale.group_results.splice(index, 1)
                })

            }
        }
    })

})