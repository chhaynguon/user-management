<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted, ref } from 'vue';
import FuncService from '@/service/FnctionService';
import { useToast } from 'primevue';

const toast = useToast();
const fns = ref([]);
const fn = ref();
const deleteFnDialog = ref(false);
const deleteFnsDialog = ref(false);
const fnDialog = ref(false);
const dt = ref();
const selectedFns = ref([]);
const submitted = ref(false);

onMounted(async () => {
    await fetchFns();
});

const fetchFns = async () => {
    try {
        const res = await FuncService.findAll();
        console.log(res)
        fns.value = res.data;
        console.log("Functions:", fns.value)
    } catch (err) {
        console.error('Failed to fetch functions:', err);
    }
}

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

function exportCSV() {
    dt.value.exportCSV();
}

function openNew() {
    fn.value = {};
    submitted.value = false;
    fnDialog.value = true;
}
function hideDialog() {
    fnDialog.value = false;
    submitted.value = false;
}

function editFn(selectedFn) {
    fn.value = { ...selectedFn };
    fnDialog.value = true;
}

function findIndexById(id) {
    let index = -1;
    for (let i = 0; i < fns.value.length; i++) {
        if (fns.value[i].id === id) {
            index = i;
            break;
        }
    }

    return index;
}

async function saveFn() {
    submitted.value = true;

    if (fn.value.code?.trim() && fn.value.name?.trim() && fn.value.description?.trim()) {
        try {
            if (!fn.value.id) {
                // Create new function
                const res = await FuncService.create({
                    code: fn.value.code,
                    name: fn.value.name,
                    description: fn.value.description,
                });

                // Add the newly created function to the list
                fns.value.push(res.data);

                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Function Created',
                    life: 3000
                });
            } else {
                // Update existing function
                const res = await FuncService.update(fn.value.id, {
                    code: fn.value.code,
                    name: fn.value.name,
                    description: fn.value.description,
                });

                const index = findIndexById(fn.value.id);
                fns.value[index] = res.data;

                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Function Updated',
                    life: 3000
                });
            }

            // Reset form
            fnDialog.value = false;
            fn.value = {};
            submitted.value = false;

        } catch (error) {
            console.error(error);
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.response?.data?.message || 'Failed to save function',
                life: 3000
            });
        }
    }
}

function deleteFn() {
    try {
        FuncService.delete(fn.value.id); // make sure fnService has a delete method
        fns.value = fns.value.filter(u => u.id !== fn.value.id);
        refresh();
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Function Deleted', life: 3000 });
        deleteFnDialog.value = false;
        fn.value = {};
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete function', life: 3000 });
    }

}

function deleteSelectedFns() {
    try {
        const ids = selectedFns.value.map(u => u.id);
        Promise.all(ids.map(id => FuncService.delete(id))); // call API for each
        fns.value = fns.value.filter(u => !ids.includes(u.id));
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Functions Deleted', life: 3000 });
        deleteFnsDialog.value = false;
        selectedFns.value = [];
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete selected functions', life: 3000 });
    }
}

function confirmDeleteFn(selectedFn) {
    fn.value = selectedFn;
    deleteFnDialog.value = true;
}

function confirmDeleteSelected() {
    deleteFnsDialog.value = true;
}

const refresh = async () => {
    try {
        const res = await FuncService.findAll();
        fns.value = res.data;
        toast.add({ severity: 'success', summary: 'Refreshed', detail: 'Function list updated', life: 2000 });
    } catch (err) {
        console.error(err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to refresh functions', life: 3000 });
    }
}

</script>

<template>
    <div>
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="secondary" @click="confirmDeleteSelected()"
                        :disabled="!selectedFns || !selectedFns.length" />
                    <Button label="Refresh" icon="pi pi-refresh" severity="secondary" class="ml-2" @click="refresh" />
                </template>

                <template #end>
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable ref="dt" v-model:selection="selectedFns" :value="fns" dataKey="id" :paginator="true" :rows="10"
                :filters="filters" :globalFilterFields="['id', 'code', 'name', 'email']"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} functions">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Functions</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="ID" sortable style="min-width: 10rem"></Column>
                <Column field="code" header="Code" sortable style="min-width: 12rem"></Column>
                <Column field="name" header="Name" sortable style="min-width: 12rem"></Column>
                <Column field="description" header="Description" sortable style="min-width: 15rem"></Column>
                <Column field="created_at" header="Created at" sortable style="min-width: 16rem"></Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editFn(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteFn(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="fnDialog" :style="{ width: '450px' }" header="Function Details" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label for="code" class="block font-bold mb-3">Function code</label>
                    <InputText id="code" v-model.trim="fn.code" required="true" autofocus
                        :invalid="submitted && !fn.code" fluid />
                    <small v-if="submitted && !fn.code" class="text-red-500">Code is required.</small>
                </div>
                <div>
                    <label for="name" class="block font-bold mb-3">Function name</label>
                    <InputText id="name" v-model.trim="fn.name" required="true" autofocus
                        :invalid="submitted && !fn.name" fluid />
                    <small v-if="submitted && !fn.name" class="text-red-500">Name is required.</small>
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3">Description</label>
                    <InputText id="description" v-model.trim="fn.description" required="true" autofocus
                        :invalid="submitted && !fn.description" fluid />
                    <small v-if="submitted && !fn.description" class="text-red-500">Description is required.</small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveFn()" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteFnDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="fn">Are you sure you want to delete <b>{{ fn.name }}</b> function?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteFnDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deleteFn" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteFnsDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="fns">Are you sure you want to delete the selected functions?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteFnsDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedFns" />
            </template>
        </Dialog>
    </div>
</template>
