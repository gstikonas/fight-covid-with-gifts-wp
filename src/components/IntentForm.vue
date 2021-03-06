
<template>
    <form v-else ref="form" method="post" :action="endpoint" @submit.prevent="submit">

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('intent.labels.amount') }}</label>
            <div class="col-md-9 input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">{{ symbol || currency }}</span>
                </div>
                <input type="number" :step="stepSize" name="u_amount" v-model="modAmount" class="form-control" :placeholder="$t('intent.placeholders.amount', {symbol,amount})" />
            </div>
        </div>

        <div class="d-sm-flex mt-5">
            <p class="flex-shrink-0">
                <button type="submit" class="btn btn-primary btn-lg btn-block" :disabled="!canSubmit">
                    <span v-if="saving">{{ $t('intent.buttons.saving') }}</span>
                    <span v-else>{{ $t('intent.buttons.save') }}</span>
                </button>
            </p>
        </div>

    </form>
</template>

<script>

    import axios from 'axios';

    export default {

        name: 'IntentForm',

        props: {
            value: {
                // only for use with v-model
            },
            endpoint: {
                type    : String,
                required: false,
            },
            amount: {
                type: [Number,String],
                required: false,
                default: 10
            },
            integersOnly: {
                type: [Boolean,String],
                default: false
            },
            currency: {
                type: String,
                default: 'USD'
            },
            symbol: {
                type: String,
            },
            nonce: {
                type: Function,
                required: true
            }
        },

        data() {
            return {
                saving   : false,
                modAmount: this.amount,
                meta     : {}
            };
        },

        computed: {
            stepSize() {
                return this.integersOnly ? 1 : 0.01;
            },
            canSubmit() {
                return !this.saving && this.modAmount;
            }
        },
        mounted() {

        },
        methods: {
            validate()
            {
                if (!this.modAmount) {
                    this.$emit('error', $t('intent.validation.amount.required'))
                }
                return true;
            },
            async submit()
            {
                if (this.saving || !this.validate()) {
                    return;
                }

                this.saving = true;

                axios.post( this.endpoint ,this.getFormData())
                .then( response => {

                    console.log('Response:', response.data);

                    this.$emit('update:amount', this.modAmount);
                    this.$emit('input', token)
                })                
                .catch( error => {
                    this.$emit('error', error)
                })
                .finally( () => this.saving = false )
            },
            getFormData()
            {
                let data = new FormData;
                data.append('u_amount', this.modAmount);
                data.append('u_currency', this.currency);
                data.append('u_meta', this.meta);
                return this.nonce(data);
            }
        }
    };
</script>