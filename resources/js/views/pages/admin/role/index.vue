<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import TreeSelect from 'primevue/treeselect';
import { onMounted, ref } from 'vue';
import RoleService from '@/service/RoleService';
import { useToast } from 'primevue';
import FnctionService from '@/service/FnctionService';

const toast = useToast();
const roles = ref([]);
const role = ref();
const deleteRoleDialog = ref(false);
const deleteRolesDialog = ref(false);
const roleDialog = ref(false);
const dt = ref();
const selectedRoles = ref([]);
const submitted = ref(false);
const permissions = ref([]);
const rolePermissions = ref([]);

onMounted(async () => {
    await fetchRoles();
    await fetchPermissions();
});

const fetchRoles = async () => {
    try {
        const res = await RoleService.findAll(); // API returns roles with fnctions and permissions

        roles.value = res.data.map(role => {
            // Convert fnctions
            const fnctions = (role.fnctions || []).map(fnc => {
                // Ensure permissions is an array
                const permissionsArray = Array.isArray(fnc.permissions)
                    ? fnc.permissions
                    : Object.values(fnc.permissions || {});

                const children = permissionsArray.map(p => ({
                    key: p.pivot?.fnc_perm_code || `${fnc.code}.${p.code}`,
                    label: p.name,
                    fnction_code: fnc.code,
                    permission_code: p.code
                }));

                return {
                    key: fnc.code,
                    label: fnc.name,
                    children
                };
            });

            return {
                ...role,
                fnctionsTree: fnctions // this will be used in Tree MultiSelect
            };
        });

    } catch (err) {
        console.error('Failed to fetch roles:', err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to fetch roles', life: 3000 });
    }
};


const fetchPermissions = async () => {
    try {
        const res = await FnctionService.findAll();
        // MUST return: [ { code, name, permissions: [ {code, name} ] } ]

        permissions.value = res.data.map(fnc => ({
            key: fnc.code,
            label: fnc.name,
            children: fnc.permissions.map(p => ({
                key: `${fnc.code}.${p.code}`,
                label: p.name
            }))
        }));

    } catch (err) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load functions + permissions', life: 3000 });
    }
};


const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

function exportCSV() {
    dt.value.exportCSV();
}

function openNew() {
    role.value = {};
    rolePermissions.value = {};
    submitted.value = false;
    roleDialog.value = true;
}
function hideDialog() {
    roleDialog.value = false;
    submitted.value = false;
    rolePermissions.value = {};
}

function editRole(selectedRole) {
    role.value = { ...selectedRole, originalCode: selectedRole.code };

    const keys = [];

    selectedRole.fnctions?.forEach(fnc => {
        const permissionsArray = Array.isArray(fnc.permissions)
            ? fnc.permissions
            : Object.values(fnc.permissions || {}); // convert object to array

        permissionsArray.forEach(p => {
            const key = `${fnc.code}.${p.code}`;
            keys.push(key);
        });
    });

    rolePermissions.value = arrayToSelectionKeys(keys);
    roleDialog.value = true;
}

async function saveRole() {
    submitted.value = true;
    if (!role.value.code?.trim() || !role.value.name?.trim()) return;

    const selectedKeys = selectionKeys(rolePermissions.value).filter(key => key.includes("."));

    const payload = {
        code: role.value.code,
        name: role.value.name,
        description: role.value.description,
        permissions: selectedKeys.map(key => {
            const [fnction_code, permission_code] = key.split(".");
            return { fnction_code, permission_code, fnc_perm_code: key };
        })
    };


    try {
        if (!role.value.originalCode) {
            await RoleService.create(payload);
        } else {
            await RoleService.update(role.value.originalCode, payload);
        }

        toast.add({ severity: "success", summary: "Success", detail: "Role saved", life: 3000 });
        roleDialog.value = false;
        await fetchRoles();

    } catch (error) {
        toast.add({ severity: "error", summary: "Error", detail: "Failed to save role", life: 3000 });
    }
}


async function deleteRole() {
    try {
        await RoleService.delete(role.value.code); // make sure roleService has a delete method
        roles.value = roles.value.filter(u => u.code !== role.value.code);
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Role Deleted', life: 3000 });
        deleteRoleDialog.value = false;
        role.value = {};
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete role', life: 3000 });
    }
}

async function deleteSelectedRoles() {
    try {
        const codes = selectedRoles.value.map(u => u.code);
        await Promise.all(codes.map(code => RoleService.delete(code))); // call API for each
        roles.value = roles.value.filter(u => !codes.includes(u.code));
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Roles Deleted', life: 3000 });
        deleteRolesDialog.value = false;
        selectedRoles.value = [];
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete selected roles', life: 3000 });
    }
}

function confirmDeleteRole(selectedRole) {
    role.value = selectedRole;
    deleteRoleDialog.value = true;
}

function confirmDeleteSelected() {
    deleteRolesDialog.value = true;
}

const refresh = async () => {
    try {
        await fetchRoles();
        toast.add({ severity: 'success', summary: 'Refreshed', detail: 'Role list updated', life: 2000 });
    } catch (err) {
        console.error(err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to refresh roles', life: 3000 });
    }
}

function arrayToSelectionKeys(arr) {
    const obj = {};
    arr.forEach(key => {
        obj[key] = { checked: true };
    });
    return obj;
}

function selectionKeys(obj) {
    return Object.keys(obj).filter(k => obj[k].checked);
}


</script>

<template>
    <div>
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="secondary" @click="confirmDeleteSelected()"
                        :disabled="!selectedRoles || !selectedRoles.length" />
                    <Button label="Refresh" icon="pi pi-refresh" severity="secondary" class="ml-2" @click="refresh" />
                </template>

                <template #end>
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable ref="dt" v-model:selection="selectedRoles" :value="roles" dataKey="code" :paginator="true"
                :rows="10" :filters="filters" :globalFilterFields="['code', 'name']"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} roles">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Roles</h4>
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
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editRole(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteRole(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="roleDialog" :style="{ width: '450px' }" header="Role Details" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label for="code" class="block font-bold mb-3">Role code</label>
                    <InputText id="code" v-model.trim="role.code" required="true" autofocus
                        :invalid="submitted && !role.code" fluid />
                    <small v-if="submitted && !role.code" class="text-red-500">Code is required.</small>
                </div>
                <div>
                    <label for="name" class="block font-bold mb-3">Role name</label>
                    <InputText id="name" v-model.trim="role.name" required="true" :invalid="submitted && !role.name"
                        fluid />
                    <small v-if="submitted && !role.name" class="text-red-500">Name is required.</small>
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3">Description</label>
                    <InputText id="description" v-model.trim="role.description" required="true"
                        :invalid="submitted && !role.description" fluid />
                    <small v-if="submitted && !role.description" class="text-red-500">Description is required.</small>
                </div>
                <div>
                    <label for="permission" class="block font-bold mb-3">Permission</label>
                    <TreeSelect v-model="rolePermissions" :options="permissions" placeholder="Select permissions"
                        class="w-full" optionLabel="label" optionValue="key" :multiple="true" filter showClear selectionMode="checkbox" display="chip">
                        <!-- Optional dropdown icon -->
                        <template #dropdownicon>
                            <i class="pi pi-search" />
                        </template>
                        <!-- Optional footer -->
                        <template #footer>
                            <div class="px-3 pt-1 pb-2 flex">
                                <Button label="Remove All" severity="danger" text size="small" icon="pi pi-times"
                                    @click="rolePermissions = []" />
                            </div>
                        </template>
                    </TreeSelect>
                </div>


            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveRole()" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteRoleDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="role">Are you sure you want to delete <b>{{ role.name }}</b> role?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteRoleDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deleteRole" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteRolesDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="roles">Are you sure you want to delete the selected roles?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteRolesDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedRoles" />
            </template>
        </Dialog>
    </div>
</template>
