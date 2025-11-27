<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import TreeSelect from 'primevue/treeselect';
import { onMounted, ref } from 'vue';
import FncService from '@/service/FnctionService';
import { useToast } from 'primevue';
import PermissionService from '@/service/PermissionService';

const toast = useToast();
const fns = ref([]);
const fn = ref();
const deleteFnDialog = ref(false);
const deleteFnsDialog = ref(false);
const fnDialog = ref(false);
const dt = ref();
const selectedFns = ref([]);
const submitted = ref(false);
const permissions = ref([])
const treePermissions = ref([]); // transformed data

onMounted(async () => {
    await fetchFns();
    await fetchPermission();
});

const fetchFns = async () => {
    try {
        const res = await FncService.findAll();
        fns.value = res.data;
        console.log("Functions:", fns.value)
    } catch (err) {
        console.error('Failed to fetch functions:', err);
    }
}

const fetchPermission = async () => {
    try {
        const res = await PermissionService.findAll();
        permissions.value = res.data;

        // Group by function code
        const grouped = {};
        permissions.value.forEach(p => {
            const parent = p.code.split('.')[0];
            if (!grouped[parent]) grouped[parent] = { key: parent, label: parent, children: [] };
            grouped[parent].children.push({ key: p.code, label: p.name, value: p.code }); // value = full permission code
        });

        // Include children in treePermissions
        treePermissions.value = Object.values(grouped);

    } catch (err) {
        console.error("Failed to fetch permissions: ", err);
    }
};

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
    // Map existing permissions to parent codes
    const parentCodes = Array.from(
        new Set((selectedFn.permissions || []).map(p => p.code.split('.')[0]))
    );

    fn.value = {
        ...selectedFn,
        originalCode: selectedFn.code,      // Keep original code for updates
        permission_codes: parentCodes       // Bind to TreeSelect
    };

    fnDialog.value = true;
}

async function saveFn() {
    submitted.value = true;

    // Basic validation
    if (!fn.value.code?.trim() || !fn.value.name?.trim() || !fn.value.description?.trim()) {
        return;
    }

    const parentKeys = treePermissions.value.map(tp => tp.key);
    const selectedParents = Array.isArray(fn.value.permission_codes)
        ? fn.value.permission_codes.filter(code => parentKeys.includes(code))
        : [];

    const payload = {
        code: fn.value.code,
        name: fn.value.name,
        description: fn.value.description,
        permission_codes: selectedParents
    };

    try {
        const isNew = !fn.value.originalCode;

        let res;
        if (isNew) {
            res = await FncService.create(payload);
            fns.value.push(res.data);
        } else {
            // Use originalCode to find the correct function in DB
            res = await FncService.update(fn.value.originalCode, payload);
            const codex = fns.value.findIndex(f => f.code === fn.value.originalCode);
            if (codex !== -1) fns.value[codex] = res.data;
        }

        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Function saved successfully',
            life: 3000
        });

        fnDialog.value = false;
        fn.value = {};
        submitted.value = false;

    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message || 'Failed to save function',
            life: 3000
        });
    }
}

async function deleteFn() {
    try {
        await FncService.delete(fn.value.code); // make sure fnService has a delete method
        fns.value = fns.value.filter(u => u.code !== fn.value.code);
        refresh();
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Function Deleted', life: 3000 });
        deleteFnDialog.value = false;
        fn.value = {};
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete function', life: 3000 });
    }

}

async function deleteSelectedFns() {
    try {
        const codes = selectedFns.value.map(u => u.code);
        await Promise.all(codes.map(codes => FncService.delete(codes))); // call API for each
        fns.value = fns.value.filter(u => !codes.includes(u.code));
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
        const res = await FncService.findAll();
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

            <DataTable ref="dt" v-model:selection="selectedFns" :value="fns" dataKey="code" :paginator="true" :rows="10"
                :filters="filters" :globalFilterFields="['code', 'name']"
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
                <div>
                    <label for="permission" class="block font-bold mb-3">Permission</label>
                    <TreeSelect v-model="fn.permission_codes" :options="treePermissions" optionLabel="label"
                        optionValue="key" :multiple="true" selectionMode="checkbox" filter showClear display="chip"
                        placeholder="Select permissions" class="w-full">
                        <template #dropdownicon>
                            <i class="pi pi-search" />
                        </template>
                        <template #footer>
                            <div class="px-3 pt-1 pb-2 flex">
                                <Button label="Remove All" severity="danger" text size="small" icon="pi pi-times"
                                    @click="fn.permission_codes = []" />
                            </div>
                        </template>
                    </TreeSelect>


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
