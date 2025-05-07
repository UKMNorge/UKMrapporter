<template>
    <div>

            <div v-if="!answered" class="as-container container">
                <template v-if="!savingAnswer">
                    <Feedback :question="'Hvordan vil du vurdere opplevelsen din med rapportgeneratoren?'" :answers="getFeedbackAnswers()" :campaign="campaignId" :sendAnswerCallback="saveFeedback" />
                </template>
                <template v-else>
                    <div v-if="savingAnswer" class="as-card-1 as-padding-space-6">
                        <h4 class="as-text-center">Takk for din tilbakemelding!</h4>
                    </div>
                </template>
            </div>

    </div>
</template>

<script lang="ts">
import { Feedback } from 'ukm-components-vue3';
import { SPAInteraction } from 'ukm-spa/SPAInteraction';

var ajaxurl : string = (<any>window).ajaxurl; // Kommer fra global

export default {
    mounted() {
        this._fetchFeedback(this.campaignId);
    },
    components : {
        Feedback : Feedback
    },
    data() {
        return {
            campaignId : 1,
            spaInteraction: new SPAInteraction(null, ajaxurl),
            answered : true,
            savingAnswer : false,
        }
    },
    methods: {
        async _fetchFeedback(campaignId : number) {
            var data : any = {
                action: 'UKMrapporter_ajax',
                controller: '/feedback/hasUserAnsweredFeedback',
            };
            
            var response = await this.spaInteraction.runAjaxCall('/', 'POST', data);
            this.answered = response.hasAnswered == true ? true : false;
        },
        getFeedbackAnswers() : Array<{id : String, text : String, iconClass : String}> {
            return [
            {id: '1', text: '', iconClass: 'mdi-emoticon-angry-outline'},
                {id: '2', text: '', iconClass: 'mdi-emoticon-sad-outline'},
                {id: '3', text: '', iconClass: 'mdi-emoticon-neutral-outline'},
                {id: '4', text: '', iconClass: 'mdi-emoticon-happy-outline'},
                {id: '5', text: '', iconClass: 'mdi-emoticon-excited-outline'},
            ];
        },
        saveFeedback(answer : {id : String, text : String, iconClass : String}, question : String, campaignId : String) {
            // Save to server
            var data : any = {
                action: 'UKMrapporter_ajax',
                controller: 'feedback/saveFeedback',
                campaignId: campaignId,
                question : question,
                answerId : answer.id,
                answerText : answer.text,
            };

            this.spaInteraction.runAjaxCall('/', 'POST', data, true); // Silent true
            
            this.savingAnswer = true;
            setTimeout(() => {
                // this.savingAnswer = false;
                this.answered = true;
            }, 3000)
        }
    }
}
</script>


<style scoped>

</style>


