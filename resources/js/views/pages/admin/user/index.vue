<script setup>
import { FilterMatchMode } from "@primevue/core/api";
import { onMounted, reactive, ref, watch, computed } from "vue";
import UserService from "@/service/UserService";
import GroupService from "@/service/GroupService";
import RoleService from "@/service/RoleService";
import { useToast } from "primevue";
import FnctionService from "@/service/FnctionService";
import PermissionService from "@/service/PermissionService";
import TreeSelect from 'primevue/treeselect';

// Primevue
const toast = useToast();

// Data
const users = ref([]);
const groups = ref([]);
const roles = ref([]);
const fnctions = ref([]);
const permissions = ref([]);

// Form model
const user = ref({});
const groupCodes = ref([]); // selected groups
const roleCodes = ref([]); // selected roles
const fnctionCodes = ref([]); // selected functions
const fnctionPermissions = reactive({});

const dt = ref();
const deleteUserDialog = ref(false);
const deleteUsersDialog = ref(false);
const userDialog = ref(false);
const selectedUsers = ref([]);
const submitted = ref(false);
const selectedFnctionPermissionKeys = ref({});
const currentUserPermissions = ref([]);

onMounted(async () => {
    await Promise.all([
        fetchUsers(),
        fetchGroups(),
        fetchRoles(),
        fetchFnctions(),
        fetchPermissions(),
    ]);

    // try {
    //     const res = await UserService.currentUserPermissions();
    //     currentUserPermissions.value = res.data;
    // } catch (err) {
    //     console.error(" Failed to fetch user permissions:", err);
    // }
});

const hasPermission = (fncPermCode) => {
    return currentUserPermissions.value.includes(fncPermCode);
};


const fetchUsers = async () => {
    try {
        const res = await UserService.findAll();
        users.value = res.data;
    } catch (err) {
        console.error("Failed to fetch users:", err);
    }
};
const fetchGroups = async () => {
    try {
        const res = await GroupService.findAll();
        groups.value = res.data;
    } catch (err) {
        console.error("Failed to fetch groups:", err);
    }
};
const fetchRoles = async () => {
    try {
        const res = await RoleService.findAll();
        roles.value = res.data;
    } catch (err) {
        console.error("Failed to fetch roles:", err);
    }
};
const fetchFnctions = async () => {
    try {
        const res = await FnctionService.findAll();
        fnctions.value = res.data;
    } catch (err) {
        console.error("Failed to fetch functions:", err);
    }
};
const fetchPermissions = async () => {
    try {
        const res = await PermissionService.findAll();
        permissions.value = res.data;
    } catch (err) {
        console.error("Failed to fetch permissions:", err);
    }
};

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

function exportCSV() {
    dt.value.exportCSV();
}

function openNew() {
    user.value = {};
    groupCodes.value = [];
    roleCodes.value = [];
    fnctionCodes.value = [];
    submitted.value = false;

    // RESET TreeSelect properly
    selectedFnctionPermissionKeys.value = {};

    // reset permission store
    Object.keys(fnctionPermissions).forEach(key => delete fnctionPermissions[key]);

    userDialog.value = true;
}

function hideDialog() {
    userDialog.value = false;
    submitted.value = false;
    selectedFnctionPermissionKeys.value = {};
    Object.keys(fnctionPermissions).forEach(key => delete fnctionPermissions[key]);
}

function editUser(selectedUser) {

    user.value = { ...selectedUser, password: "" };
    groupCodes.value = selectedUser.groups?.map(g => g.code) || [];
    roleCodes.value = selectedUser.roles?.map(r => r.code) || [];
    fnctionCodes.value = selectedUser.fnctions?.map(f => f.code) || [];
    // Reset permissions object
    selectedFnctionPermissionKeys.value = {};
    Object.keys(fnctionPermissions).forEach(key => delete fnctionPermissions[key]);

    (selectedUser.fnctions || []).forEach(f => {
        const selectedPerms = f.selectedPermission || [];
        // Add permission nodes
        (f.permissions || []).forEach(p => {

            if (selectedPerms.includes(p.code)) {
                selectedFnctionPermissionKeys.value = {
                    ...selectedFnctionPermissionKeys.value,
                    [`${f.code}.${p.code}`]: { checked: true }
                };
            }
        });

    });
    console.log(selectedFnctionPermissionKeys.value);

    // Load selected permissions for each function
    fnctionCodes.value.forEach(fnCode => {
        const fnObj = selectedUser.fnctions?.find(f => f.code === fnCode);
        fnctionPermissions[fnCode] = fnObj?.permissions?.map(p => p.code) || [];
    });


    userDialog.value = true;
}

function findIndexById(id) {
    return users.value.findIndex(u => u.id === id);
}

async function saveUser() {
    submitted.value = true;

    if (!user.value.name || !user.value.email || (!user.value.id && !user.value.password)) {
        return;
    }

    // Extract function + permission keys from TreeSelect
    const funcKeys = Object.keys(selectedFnctionPermissionKeys.value).filter(
        key => selectedFnctionPermissionKeys.value[key].checked && key.includes(".")
    );

    // Determine selected functions (pure function nodes without ".")
    fnctionCodes.value = Object.keys(selectedFnctionPermissionKeys.value)
        .filter(key => selectedFnctionPermissionKeys.value[key].checked && !key.includes("."));

    // Group permissions by function code
    const fnction_permission = {};
    funcKeys.forEach(k => {
        const [fnCode, permCode] = k.split(".");
        if (!fnction_permission[fnCode]) fnction_permission[fnCode] = [];
        fnction_permission[fnCode].push(permCode);
    });

    // Convert to array of objects for API
    const fnction_permission_array = Object.entries(fnction_permission).map(
        ([fnCode, permission_codes]) => ({
            fnction_code: fnCode,
            permission_codes
        })
    );


    const payload = {
        name: user.value.name,
        email: user.value.email,
        password: user.value.password || undefined,
        group_code: groupCodes.value,
        role_code: roleCodes.value,
        fnction_code: fnctionCodes.value,
        fnction_permission: fnction_permission_array
    };

    try {
        let res;
        if (!user.value.id) {
            res = await UserService.create(payload);
            users.value.push(res.data);
            toast.add({ severity: "success", summary: "Success", detail: "User Created", life: 3000 });
        } else {
            res = await UserService.update(user.value.id, payload);
            const index = findIndexById(user.value.id);
            users.value[index] = res.data;
            toast.add({ severity: "success", summary: "Success", detail: "User Updated", life: 3000 });
        }

        // Reset form
        userDialog.value = false;
        user.value = {};
        submitted.value = false;
        selectedFnctionPermissionKeys.value = {};
    } catch (error) {
        console.error(error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: error.response?.data?.message || "Failed to save user",
            life: 3000
        });
    }
}


async function deleteUser() {
    try {
        await UserService.delete(user.value.id);
        users.value = users.value.filter(u => u.id !== user.value.id);
        refresh();
        toast.add({
            severity: "success",
            summary: "Successful",
            detail: "User Deleted",
            life: 3000,
        });
        deleteUserDialog.value = false;
        user.value = {};
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to delete user",
            life: 3000,
        });
    }
}

async function deleteSelectedUsers() {
    try {
        const ids = selectedUsers.value.map((u) => u.id);
        await Promise.all(ids.map((id) => UserService.delete(id))); // call API for each
        users.value = users.value.filter((u) => !ids.includes(u.id));
        toast.add({
            severity: "success",
            summary: "Successful",
            detail: "Users Deleted",
            life: 3000,
        });
        deleteUsersDialog.value = false;
        selectedUsers.value = [];
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to delete selected users",
            life: 3000,
        });
    }
}

function confirmDeleteUser(selectedUser) {
    user.value = selectedUser;
    deleteUserDialog.value = true;
}

function confirmDeleteSelected() {
    deleteUsersDialog.value = true;
}

const refresh = async () => {
    try {
        await fetchUsers();
        toast.add({
            severity: "success",
            summary: "Refreshed",
            detail: "User list updated",
            life: 2000,
        });
    } catch (err) {
        console.error(err);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to refresh users",
            life: 3000,
        });
    }
};

watch(fnctionCodes, (newFns) => {
    // Reset object
    Object.keys(fnctionPermissions).forEach(key => {
        if (!newFns.includes(key)) delete fnctionPermissions[key];
    });

    newFns.forEach(fnCode => {
        if (!fnctionPermissions.hasOwnProperty(fnCode)) {
            const fnObj = fnctions.value.find(f => f.code === fnCode);
            fnctionPermissions[fnCode] = fnObj?.permissions?.map(p => p.code) || [];
        }
    });
}, { immediate: true });

const listToString = (arr, key) => arr?.map(x => x[key]).join(', ') || '-';

const fnctionTree = computed(() => {
    return fnctions.value.map(fn => ({
        key: fn.code,
        label: fn.name,
        children: (fn.permissions || []).map(p => ({
            key: `${fn.code}.${p.code}`,
            label: p.name,
            data: {
                fnction_code: fn.code,
                permission_code: p.code
            }
        }))
    }));
});


</script>

<template>
    <div>
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="secondary" @click="confirmDeleteSelected()"
                        :disabled="!selectedUsers || !selectedUsers.length" />
                    <Button label="Refresh" icon="pi pi-refresh" severity="secondary" class="ml-2" @click="refresh" />
                </template>

                <template #end>
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable ref="dt" v-model:selection="selectedUsers" :value="users" dataKey="id" :paginator="true"
                :rows="10" :filters="filters" :globalFilterFields="['id', 'name', 'email']"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} users">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h4 class="m-0">Manage Users</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="ID" sortable style="min-width: 5rem"></Column>
                <Column field="name" header="Name" sortable style="min-width: 8rem"></Column>
                <Column field="email" header="Email" sortable style="min-width: 10rem"></Column>
                <Column field="created_at" header="Created at" sortable style="min-width: 10rem"></Column>
                <Column header="Groups" sortable>
                    <template #body="slotProps">
                        <span v-if="slotProps.data.groups?.length">
                            {{ listToString(slotProps.data.groups, 'code') }}
                        </span>
                        <span v-else>-</span>
                    </template>
                </Column>
                <Column header="Roles" sortable>
                    <template #body="slotProps">
                        <span v-if="slotProps.data.roles?.length">
                            {{ listToString(slotProps.data.roles, 'code') }}
                        </span>
                        <span v-else>-</span>
                    </template>
                </Column>
                <Column header="Functions" sortable>
                    <template #body="slotProps">
                        <span v-if="slotProps.data.fnctions?.length">
                            {{ listToString(slotProps.data.fnctions, 'code') }}
                        </span>
                        <span v-else>-</span>
                    </template>
                </Column>

                <Column :exportable="false" style="min-width: 8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editUser(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteUser(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="userDialog" :style="{ width: '800px' }" header="User Details" :modal="true">
            <div>
                <div>
                    <label for="name" class="block mb-3 font-bold">Username</label>
                    <InputText id="name" v-model.trim="user.name" autofocus :invalid="submitted && !user.name" fluid />
                    <small v-if="submitted && !user.name" class="text-red-500">Name is required.</small>
                </div>
                <div>
                    <label for="email" class="block my-3 font-bold">Email</label>
                    <InputText id="email" v-model.trim="user.email" :invalid="submitted && !user.email" fluid />
                    <small v-if="submitted && !user.email" class="text-red-500">Email is required.</small>
                </div>
                <div>
                    <label for="password" class="block my-3 font-bold">Password</label>
                    <InputText id="password" type="password" v-model.trim="user.password"
                        :invalid="submitted && !user.password && !user.id" fluid />
                    <small v-if="submitted && !user.password && !user.id" class="text-red-500">Password is
                        required.</small>
                </div>
                <div class="col-span-2 mt-2">
                    <div>
                        <label class="block mb-3 font-bold">Roles</label>
                        <MultiSelect v-model="roleCodes" :options="roles" optionValue="code" optionLabel="name"
                            display="chip" placeholder="Select Roles" class="w-full" showClear />
                    </div>
                    <div class="my-3">
                        <label class="my-3 font-bold">Functions</label>
                        <TreeSelect v-model="selectedFnctionPermissionKeys" :options="fnctionTree"
                            selectionMode="checkbox" display="chip" showClear placeholder="Select functions"
                            :propagateSelectionUp="true" :propagateSelectionDown="true" class="w-full" />
                    </div>
                    <div>
                        <label for="group" class="block my-3 font-bold">Group</label>
                        <MultiSelect v-model="groupCodes" :options="groups" optionLabel="name" optionValue="code"
                            multiple selectionMode="checkbox" showClear display="chip" placeholder="Select Groups"
                            class="w-full" />
                    </div>
                </div>

            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveUser()" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteUserDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="user">Are you sure you want to delete <b>{{ user.name }}</b>?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteUserDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deleteUser" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteUsersDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="users">Are you sure you want to delete the selected users?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteUsersDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedUsers" />
            </template>
        </Dialog>
    </div>
</template>
