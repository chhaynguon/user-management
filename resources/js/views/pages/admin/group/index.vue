<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted, ref } from 'vue';
import GroupService from '@/service/GroupService';
import { useToast } from 'primevue';

const toast = useToast();
const groups = ref([]);
const group = ref();
const deleteGroupDialog = ref(false);
const deleteGroupsDialog = ref(false);
const groupDialog = ref(false);
const dt = ref();
const selectedGroups = ref([]);
const submitted = ref(false);

onMounted(async () => {
    await fetchGroups();
});

const fetchGroups = async () => {
    try {
        const res = await GroupService.findAll();
        console.log(res)
        groups.value = res.data;
        console.log("Groups:", groups.value)
    } catch (err) {
        console.error('Failed to fetch groups:', err);
    }
}

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

function exportCSV() {
    dt.value.exportCSV();
}

function openNew() {
    group.value = {};
    submitted.value = false;
    groupDialog.value = true;
}
function hideDialog() {
    groupDialog.value = false;
    submitted.value = false;
}

function editGroup(selectedGroup) {
    group.value = { ...selectedGroup };
    groupDialog.value = true;
}

async function saveGroup() {
    submitted.value = true;

    if (group.value.code?.trim() && group.value.name?.trim() && group.value.description?.trim()) {
        try {
            if (!group.value.id) {
                // Create new group
                const res = await GroupService.create({
                    code: group.value.code,
                    name: group.value.name,
                    description: group.value.description,
                });

                // Add the newly created group to the list
                groups.value.push(res.data);

                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Group Created',
                    life: 3000
                });
            } else {
                // Update existing group
                const res = await GroupService.update(group.value.id, {
                    code: group.value.code,
                    name: group.value.name,
                    description: group.value.description,
                });

                groups.value[index] = res.data;

                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'group Updated',
                    life: 3000
                });
            }

            // Reset form
            groupDialog.value = false;
            group.value = {};
            submitted.value = false;

        } catch (error) {
            console.error(error);
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.response?.data?.message || 'Failed to save group',
                life: 3000
            });
        }
    }
}

function deleteGroup() {
    try {
        GroupService.delete(group.value.id); // make sure groupService has a delete method
        groups.value = groups.value.filter(u => u.id !== group.value.id);
        refresh();
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Group Deleted', life: 3000 });
        deleteGroupDialog.value = false;
        group.value = {};
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete group', life: 3000 });
    }

}

function deleteSelectedGroups() {
    try {
        const ids = selectedGroups.value.map(u => u.id);
        Promise.all(ids.map(id => GroupService.delete(id))); // call API for each
        groups.value = groups.value.filter(u => !ids.includes(u.id));
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Groups Deleted', life: 3000 });
        deleteGroupsDialog.value = false;
        selectedGroups.value = [];
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete selected groups', life: 3000 });
    }
}

function confirmDeleteGroup(selectedGroup) {
    group.value = selectedGroup;
    deleteGroupDialog.value = true;
}

function confirmDeleteSelected() {
    deleteGroupsDialog.value = true;
}

const refresh = async () => {
    try {
        const res = await GroupService.findAll();
        groups.value = res.data;
        toast.add({ severity: 'success', summary: 'Refreshed', detail: 'Group list updated', life: 2000 });
    } catch (err) {
        console.error(err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to refresh groups', life: 3000 });
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
                        :disabled="!selectedGroups || !selectedGroups.length" />
                    <Button label="Refresh" icon="pi pi-refresh" severity="secondary" class="ml-2" @click="refresh" />
                </template>

                <template #end>
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable ref="dt" v-model:selection="selectedGroups" :value="groups" dataKey="code" :paginator="true"
                :rows="10" :filters="filters" :globalFilterFields="['code', 'name', 'email']"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} groups">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Groups</h4>
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
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editGroup(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteGroup(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="groupDialog" :style="{ width: '450px' }" header="Group Details" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label for="code" class="block font-bold mb-3">Group code</label>
                    <InputText id="code" v-model.trim="group.code" required="true" autofocus
                        :invalid="submitted && !group.code" fluid />
                    <small v-if="submitted && !group.code" class="text-red-500">Code is required.</small>
                </div>
                <div>
                    <label for="name" class="block font-bold mb-3">Group name</label>
                    <InputText id="name" v-model.trim="group.name" required="true" autofocus
                        :invalid="submitted && !group.name" fluid />
                    <small v-if="submitted && !group.name" class="text-red-500">Name is required.</small>
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3">Description</label>
                    <InputText id="description" v-model.trim="group.description" required="true" autofocus
                        :invalid="submitted && !group.description" fluid />
                    <small v-if="submitted && !group.description" class="text-red-500">Description is required.</small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveGroup()" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteGroupDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="group">Are you sure you want to delete <b>{{ group.name }}</b> group?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteGroupDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deleteGroup" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteGroupsDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="groups">Are you sure you want to delete the selected groups?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteGroupsDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedGroups" />
            </template>
        </Dialog>
    </div>
</template>
