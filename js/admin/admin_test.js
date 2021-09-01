import Vue from 'vuejs'
import Axios from 'axios'

document.addEventListener('DOMContentLoaded', function () {

    window.app = new Vue({
        el: '.wrap.test',
        data: {
            search: '',
            currentGroupResultType: 'subscale-key-data',
            currentGroupName: '',
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
            api: Axios.create({
                baseURL: localURLs.rest,
                headers: {
                    'content-type': 'application/json',
                    'X-WP-Nonce': document.querySelector('#wizard-wrapper').getAttribute('data-nonce')
                }
            }),
            wizardMaxStep: 0,
            wizardActiveStep: 0,
            forms: [],
            fields: [],
            groups: [],
            test: {
                form_id: -1,
                subscales: []
            },
            active: -1,
            fields: [],
            lastId: 0,
        },
        beforeMount() {

            this.api.get('gravityscores/v1/forms').then((response) => {
                this.forms = response.data
            });

            this.api.get('gravityscores/v1/groups').then((response) => {
                this.groups = response.data.map(function (group) {
                    return group.name;
                })
            });

        },
        computed: {
            groupResultFilledOut: function () {

                let currentGroupForm = this.currentGroupForm
                let result = false;

                if (this.currentGroupName.trim() == '') {
                    return result
                }

                Object.keys(currentGroupForm['data']['subscale-key-data']).forEach(function (key) {
                    if (currentGroupForm['data']['subscale-key-data'][key] != 0)
                        result = true
                })

                return result

            },
            currentGroupResult: function () {

                let currentGroupName = this.currentGroupName

                let currentGroupResult = this.currentElement.group_results.filter((group) => {
                    return group.group == currentGroupName
                })

                return currentGroupResult[0]
            },
            currentElement: function () {

                if (this.wizardActiveStep == 0) {
                    return Array.from(this.forms).filter((form) => {
                        return form.id == this.active
                    })[0]
                } else {
                    return Array.from(this.test.subscales).filter((subscale) => {
                        return subscale.tempId == this.active
                    })[0]
                }

            },
            currentForm: function () {
                return Array.from(this.forms).filter((form) => {
                    return form.id == this.test.form_id
                })[0]
            },
            searchForms: function () {

                if (this.search.trim() == "") {
                    return this.forms
                }

                let forms = this.forms.filter((form) => {

                    let search_chars = this.search.toLowerCase().trim().split('')
                    let chars = (form.title + form.description).toLowerCase().split('')

                    for (let index in search_chars) {
                        if (!chars.includes(search_chars[index])) {
                            return false;
                        }
                        chars.splice(chars.indexOf(search_chars[index]), 1);
                    }
                    return true
                })

                if (forms.length > 0) {
                    this.activate(forms[0].id)
                }
                return forms
            },
            currentSubscaleIndex: function () {

                return this.test.subscales.indexOf(this.currentElement)
            }

        },
        methods: {
            // WIZARD
            wizardStep(target = null) {

                if (target == this.wizardActiveStep || (target != null && target > this.wizardMaxStep)) {
                    return
                }

                if (this.wizardActiveStep == 1) {
                    this.unsetSubscalesState();
                }

                if (target == null) {
                    if (this.wizardActiveStep == this.wizardMaxStep) {
                        this.wizardMaxStep++;
                    }

                    this.wizardActiveStep++;
                } else if (this.wizardMaxStep >= target) {

                    this.wizardActiveStep = target

                }

                this.active = -1

                if (target == 0) {
                    this.active = this.test.form_id
                    this.search = ""
                }

            },
            wizardActive(step) {
                return step == this.wizardActiveStep
            },
            wizardDisabled(step) {
                return this.wizardMaxStep < step
            },
            activate(id, event = null) {
                if (event != null) {
                    event.currentTarget.focus();
                }
                this.active = id
            },
            nextId() {
                this.lastId++
                return this.lastId
            },
            log(data) {
                console.log(data)
                return data
            },
            finishSelectForm() {

                if (this.test.form_id !== this.currentElement.id) {

                    if (this.test.subscales.length !== 0) {
                        if (!confirm('Changing the form will destroy your progress. Do you want to continue?')) {
                            return false;
                        }
                        this.wizardMaxStep = 1
                    }

                    Vue.set(this.test, 'form_id', this.currentElement.id)
                    Vue.set(this.test, 'subscales', [])

                    this.api.get('gravityscores/v1/fields/' + this.currentElement.id).then((response) => {

                        this.fields = response.data

                        for (let field in this.fields) {
                            this.fields[field]['subscales'] = []
                        }
                    });


                }
                this.wizardStep()
            },
            submitSubscale(event) {

                let index = -1
                let name = document.querySelector('#subscale-name-text').value.trim()
                let description = document.querySelector('#subscale-description-textarea').value.trim()

                if (name === '') {
                    return
                }

                if (event.srcElement.nodeName == 'TEXTAREA') {
                    document.querySelector('#subscale-description-textarea').value += "\n"
                    return;
                }

                let same = Array.from(this.test.subscales).filter((subscale) => {
                    return subscale.name == name
                })

                if (same.length > 0) {

                    if (same[0].tempId != this.active) {
                        same[0].state = 'alert';
                        setTimeout(function () { if (same[0] != null) same[0].state = 'normal' }, 3000)
                        return
                    }
                }

                if (this.test.subscales.length == 0) {
                    window.onbeforeunload = function () {
                        return 'Do you really want to leave the page? All your progress will be lost.'
                    }
                }

                let tempId = 0;

                if (this.active != -1) {
                    let element = Array.from(this.test.subscales).filter(subscale => {
                        return subscale.tempId == this.active
                    })[0]

                    tempId = element.tempId
                    index = this.test.subscales.indexOf(element)
                } else {
                    tempId = this.nextId()
                    index = this.test.subscales.length
                }

                Vue.set(this.test.subscales, index, {
                    tempId: tempId,
                    state: 'success',
                    name: name,
                    description: description,
                    group_results: [],
                    evaluables: []
                })
                this.active = -1
                let subscale = this.test.subscales[index]

                document.querySelector('#subscale-name-text').value = ''
                document.querySelector('#subscale-description-textarea').value = ''
                setTimeout(function () { if (subscale != null) subscale.state = 'normal' }, 3000)

                this.$nextTick(() => {
                    document.querySelector('#subscale-name-text').focus()
                })
            },
            toggleSubscaleEditMode(subscale) {

                let nameField = document.querySelector('#subscale-name-text')
                let descriptionField = document.querySelector('#subscale-description-textarea')

                if (this.active == subscale.tempId) {
                    this.active = -1
                    nameField.value = ''
                    descriptionField.value = ''
                } else {
                    this.active = subscale.tempId
                    nameField.value = subscale.name
                    descriptionField.value = subscale.description
                }

            },
            focusInputSubscaleName() {

                document.querySelector('#subscale-name-text').focus()

            },
            unsetSubscalesState() {
                Array.from(this.test.subscales).forEach((subscale) => {
                    subscale.state = "normal"
                })
            },
            selectSubscalePage(subscale) {
                if (this.active == subscale.tempId) {
                    this.active = -1
                } else {
                    this.active = subscale.tempId

                    if (this.wizardActiveStep == 2) {

                        this.$nextTick(() => {

                            let fieldId = ''
                            let object = {}

                            for (let field in this.fields) {

                                if (!this.fields[field].usable)
                                    continue

                                fieldId = this.fields[field].field_id.toString() + ((this.fields[field].sub_question == null) ? '' : '_' + this.fields[field].sub_question.toString())
                                object = document.querySelector('#add-to-subscale' + fieldId)

                                if (this.fields[field].subscales.includes(subscale.tempId)) {
                                    document.querySelector('#add-to-subscale' + fieldId).checked = true
                                } else {
                                    document.querySelector('#remove-from-subscale' + fieldId).checked = true
                                }

                                if (this.fields[field].hasOwnProperty('weights') && this.fields[field].weights.hasOwnProperty(subscale.tempId.toString())) {
                                    document.querySelector('#question-weight-' + fieldId).value = this.fields[field].weights[subscale.tempId.toString()]
                                } else {
                                    document.querySelector('#question-weight-' + fieldId).value = 1
                                }


                            }
                        })
                    }


                }
            },
            toggleFieldInSubscale(event, field, subscale) {

                if (event.currentTarget.value == 'yes' && !field.subscales.includes(subscale.tempId)) {

                    Vue.set(this.fields[this.fields.indexOf(field)].subscales, field.subscales.length, subscale.tempId)

                } else if (field.subscales.includes(subscale.tempId)) {

                    Vue.delete(this.fields[this.fields.indexOf(field)].subscales, field.subscales.indexOf(subscale.tempId))

                }

                this.$forceUpdate()

            },
            nextSubscaleName() {
                let subscaleIndex = this.test.subscales.indexOf(this.currentElement)
                return this.test.subscales[(subscaleIndex + 1) % this.test.subscales.length].name
            },
            nextSubscale() {

                let subscaleIndex = this.test.subscales.indexOf(this.currentElement)
                this.selectSubscalePage(this.test.subscales[(subscaleIndex + 1) % this.test.subscales.length])
            },
            changeWeight(event, field, subscale) {

                if (!field.hasOwnProperty('weights')) {
                    Vue.set(field, 'weights', {})
                }

                field.weights[subscale.tempId] = event.currentTarget.value

            },
            addOrEditGroupResult() {

                let index = this.currentElement.group_results.length
                let keyData = this.currentGroupForm['data']['subscale-key-data']

                if (!this.groups.includes(this.currentGroupName)) {
                    this.groups.push(this.currentGroupName)
                }

                if (this.currentGroupResult != undefined) {
                    index = this.currentElement.group_results.indexOf(this.currentGroupResult)
                }

                Vue.set(this.currentElement.group_results, index, {
                    'group': this.currentGroupName,
                    'data': {
                        'subscale-key-data': Object.assign({}, keyData)
                    }
                })

                this.currentGroupName = ''

                Object.keys(keyData).forEach((key) => {
                    keyData[key] = 0
                })

                this.$forceUpdate
            },
            toggleEditSubscale(groupName) {
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
            submit() {

                let exportData = {
                    tests: [{
                        form_id: this.test.form_id,
                        subscales: this.test.subscales.map((subscale) => {

                            let associated_fields = this.fields.filter((field) => {
                                return field.usable && field.subscales.includes(subscale.tempId)
                            })


                            return {
                                'name': subscale.name,
                                'description': subscale.description,
                                'group_results': subscale.group_results.map(group_result => {
                                    return group_result
                                }),
                                'evaluables': associated_fields.map(field => {

                                    let weight = 1

                                    if (field.hasOwnProperty('weights')) {

                                        if (field.weights.hasOwnProperty(subscale.tempId)) {
                                            weight = field.weights[subscale.tempId]
                                        }

                                    }

                                    return {
                                        field_id: field.field_id,
                                        sub_question: field.sub_question,
                                        type: field.type,
                                        weight: weight
                                    }
                                }),
                            }
                        })
                    }]
                }

                this.api.post('gravityscores/v1/import/', exportData).then((response) => {
                    window.location = localURLs.home + '/wp-admin/admin.php?page=gravityscores_tests'
                });

            }
        }
    })

})