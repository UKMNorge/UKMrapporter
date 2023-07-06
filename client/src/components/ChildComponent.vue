<template>
  <div>
    <h4>Child component with title: {{ title }} (ChildComponent.vue)</h4> 
  </div>
</template>
<script lang="ts">
import MyObject from './../objects/myObject'
import MainComponent from './MainComponent.vue';

export default {
    extends : MainComponent,
    props: {
        title: String,
    },
    data() {
        return {

            myObject : new MyObject()
        }
    },
    methods : {
        init(){
            console.log('ChildComponent.vue: init()');
            var myObjMethod = this.myObject.sayHello();
            console.log('From MyObject: ' + myObjMethod);
            
            // Kall super kompontent
            this.IaMsuperMethod();
        }
    }
}

</script>