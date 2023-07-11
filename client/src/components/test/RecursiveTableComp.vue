<template>
    <div>
      <template v-if="isObject()">
        <div class="outer">
          <div class="inner" v-for="(node, key) in obj.getChildren()" :key="key">
            <RecursiveTableComp :obj="node" :parents="[...parents, node]" />
            <!-- Write content just if it is leaf node -->
            <div class="row-test" v-if="node.getChildren().length < 1">
                <!-- {{  parents  }} -->
                <div v-for="(parent, key) in parents" :key="key">
                    <h4>{{ parent.getRepresentativeName() }}</h4>
                    <!-- get all keys available and call methods -->
                </div>
                <h5>{{ node.getRepresentativeName() }}: {{ node.getId() }}</h5>
            </div>
          </div>
        </div>
      </template>
    </div>
  </template>
  
  <script setup lang="ts">
    const props = defineProps<{
        obj : any,
        parents : any[],
    }>();

    // props: {
    //   obj: {
    //     type: [Object, String, Number, Boolean, Array],
    //     required: true
    //   }
    // },
    // computed: {
    function isObject() {
        return typeof props.obj === 'object' && props.obj.getChildren().length > 0;
    }
    // }
  </script>

<style scoped>
    .outer {
        border: solid 1px;
        padding: 22px;
    }
    .inner {
        border: solid 1px sienna;
        padding: 22px;
    }
    .row-test {
        display: flex;
    }
</style>