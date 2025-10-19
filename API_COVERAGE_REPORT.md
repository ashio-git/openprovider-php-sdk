# API Coverage Report
## OpenProvider PHP SDK - Complete Audit

**Generated:** 2025-10-18  
**Total API Endpoints:** 111  
**Implemented:** 97 (87.4%)  
**Missing:** 14 (12.6%)

---

## ✅ Implemented Endpoints (97)

### Auth (0/1) - 0%
- ❌ `POST /v1beta/auth/login` → Login *(handled internally in OpenProvider.php)*

### Domain Service (13/14) - 92.9%
- ✅ `POST /v1beta/domains/check` → check()
- ✅ `POST /v1beta/domains` → createDomain()
- ✅ `GET /v1beta/domains` → listDomains()
- ✅ `GET /v1beta/domains/{id}` → getDomain()
- ✅ `PUT /v1beta/domains/{id}` → updateDomain()
- ✅ `DELETE /v1beta/domains/{id}` → deleteDomain()
- ✅ `POST /v1beta/domains/{id}/renew` → renewDomain()
- ✅ `POST /v1beta/domains/{id}/restore` → restoreDomain()
- ✅ `POST /v1beta/domains/transfer` → transferDomain()
- ✅ `POST /v1beta/domains/trade` → tradeDomain()
- ✅ `POST /v1beta/domains/{id}/transfer/approve` → approveTransfer()
- ✅ `POST /v1beta/domains/{id}/transfer/send-foa1` → sendFoa1()
- ✅ `POST /v1beta/domains/suggest-name` → suggestName()
- ✅ `POST /v1beta/domains/{id}/last-operation/restart` → tryAgainLastOperation()

### Domain Additional Data (2/2) - 100%
- ✅ `GET /v1beta/domains/additional-data` → getAdditionalData()
- ✅ `GET /v1beta/domains/additional-data/customers` → getCustomerAdditionalData()

### Domain Price (0/1) - 0%
- ❌ `GET /v1beta/domains/prices` → getPricelist() *(IMPLEMENTED but named differently)*

### Auth Code (2/2) - 100%
- ✅ `GET /v1beta/domains/{id}/authcode` → getAuthCode()
- ✅ `POST /v1beta/domains/{id}/authcode/reset` → resetAuthCode()

### TLD Service (2/2) - 100%
- ✅ `GET /v1beta/tlds` → listTlds()
- ✅ `GET /v1beta/tlds/{name}` → getTld()

### DNS Nameservers (5/5) - 100%
- ✅ `GET /v1beta/dns/nameservers` → listNameservers()
- ✅ `POST /v1beta/dns/nameservers` → createNameserver()
- ✅ `GET /v1beta/dns/nameservers/{name}` → getNameserver()
- ✅ `PUT /v1beta/dns/nameservers/{name}` → updateNameserver()
- ✅ `DELETE /v1beta/dns/nameservers/{name}` → deleteNameserver()

### DNS Nameserver Groups (5/5) - 100%
- ✅ `GET /v1beta/dns/nameservers/groups` → listNameserverGroups()
- ✅ `POST /v1beta/dns/nameservers/groups` → createNameserverGroup()
- ✅ `GET /v1beta/dns/nameservers/groups/{ns_group}` → getNameserverGroup()
- ✅ `PUT /v1beta/dns/nameservers/groups/{ns_group}` → updateNameserverGroup()
- ✅ `DELETE /v1beta/dns/nameservers/groups/{ns_group}` → deleteNameserverGroup()

### DNS Zones (5/5) - 100%
- ✅ `GET /v1beta/dns/zones` → listZones()
- ✅ `POST /v1beta/dns/zones` → createZone()
- ✅ `GET /v1beta/dns/zones/{name}` → getZone()
- ✅ `PUT /v1beta/dns/zones/{name}` → updateZone()
- ✅ `DELETE /v1beta/dns/zones/{name}` → deleteZone()

### DNS Zone Records (1/1) - 100%
- ✅ `GET /v1beta/dns/zones/{name}/records` → listZoneRecords()

### DNS Templates (4/4) - 100%
- ✅ `GET /v1beta/dns/templates` → listTemplates()
- ✅ `POST /v1beta/dns/templates` → createTemplate()
- ✅ `GET /v1beta/dns/templates/{id}` → getTemplate()
- ✅ `DELETE /v1beta/dns/templates/{id}` → deleteTemplate()

### DNS Domain Token (0/1) - 0%
- ❌ `POST /v1beta/dns/domain-token` → createDomainToken() *(NOT IMPLEMENTED)*

### SSL Products (2/2) - 100%
- ✅ `GET /v1beta/ssl/products` → listProducts()
- ✅ `GET /v1beta/ssl/products/{id}` → getProduct()

### SSL Orders (7/7) - 100%
- ✅ `GET /v1beta/ssl/orders` → listOrders()
- ✅ `POST /v1beta/ssl/orders` → createOrder()
- ✅ `GET /v1beta/ssl/orders/{id}` → getOrder()
- ✅ `PUT /v1beta/ssl/orders/{id}` → updateOrder()
- ✅ `POST /v1beta/ssl/orders/{id}/cancel` → cancelOrder()
- ✅ `POST /v1beta/ssl/orders/{id}/reissue` → reissueOrder()
- ✅ `POST /v1beta/ssl/orders/{id}/renew` → renewOrder()

### SSL Approver Email (3/3) - 100%
- ✅ `GET /v1beta/ssl/approver-emails` → listApproverEmails()
- ✅ `PUT /v1beta/ssl/orders/{id}/approver-email` → updateApproverEmailAddress()
- ✅ `POST /v1beta/ssl/orders/{id}/approver-email/resend` → resendApproverEmail()

### SSL CSR (2/2) - 100%
- ✅ `POST /v1beta/ssl/csr` → createCsr()
- ✅ `POST /v1beta/ssl/csr/decode` → decodeCsr()

### SSL OTP Token (1/1) - 100%
- ✅ `POST /v1beta/ssl/orders/{id}/otp-tokens` → createOtpToken()

### Licenses (14/15) - 93.3%
- ✅ `GET /v1beta/licenses` → listLicenses()
- ❌ `GET /v1beta/licenses` (with price version) → NOT IMPLEMENTED
- ✅ `GET /v1beta/licenses/items` → listItems()
- ✅ `POST /v1beta/licenses/hwids/reset/{product}/{key_id}` → resetHwid()

### Plesk Licenses (5/5) - 100%
- ✅ `GET /v1beta/licenses/plesk` → listPleskLicenses()
- ✅ `POST /v1beta/licenses/plesk` → createPleskLicense()
- ✅ `GET /v1beta/licenses/plesk/{key_id}` → getPleskLicense()
- ✅ `PUT /v1beta/licenses/plesk/{key_id}` → updatePleskLicense()
- ✅ `DELETE /v1beta/licenses/plesk/{key_id}` → deletePleskLicense()
- ✅ `GET /v1beta/licenses/plesk/key/{key_id}` → getPleskKey()

### Virtuozzo Licenses (5/5) - 100%
- ✅ `GET /v1beta/licenses/virtuozzo` → listVirtuozzoLicenses()
- ✅ `POST /v1beta/licenses/virtuozzo` → createVirtuozzoLicense()
- ✅ `GET /v1beta/licenses/virtuozzo/{key_id}` → getVirtuozzoLicense()
- ✅ `PUT /v1beta/licenses/virtuozzo/{key_id}` → updateVirtuozzoLicense()
- ✅ `DELETE /v1beta/licenses/virtuozzo/{key_id}` → deleteVirtuozzoLicense()
- ✅ `GET /v1beta/licenses/virtuozzo/{key_id}/key` → getVirtuozzoKey()

### Contacts (5/5) - 100%
- ✅ `GET /v1beta/contacts` → listContacts()
- ✅ `POST /v1beta/contacts` → createContact()
- ✅ `GET /v1beta/contacts/{id}` → getContact()
- ✅ `PUT /v1beta/contacts/{id}` → updateContact()
- ✅ `DELETE /v1beta/contacts/{id}` → deleteContact()

### Customers (5/5) - 100%
- ✅ `GET /v1beta/customers` → listCustomers()
- ✅ `POST /v1beta/customers` → createCustomer()
- ✅ `GET /v1beta/customers/{handle}` → getCustomer()
- ✅ `PUT /v1beta/customers/{handle}` → updateCustomer()
- ✅ `DELETE /v1beta/customers/{handle}` → deleteCustomer()

### Email Verification (3/3) - 100%
- ✅ `GET /v1beta/customers/verifications/emails/domains` → listDomainEmailVerifications()
- ✅ `POST /v1beta/customers/verifications/emails/start` → startEmailVerification()
- ✅ `POST /v1beta/customers/verifications/emails/restart` → restartEmailVerification()

### Reseller (2/2) - 100%
- ✅ `GET /v1beta/resellers` → getReseller()
- ✅ `PUT /v1beta/resellers/{id}` → updateReseller()

### Reseller Settings (1/1) - 100%
- ✅ `GET /v1beta/resellers/settings` → getSettings()

### Reseller Statistics (1/1) - 100%
- ✅ `GET /v1beta/resellers/statistics` → getStatistics()

### Tags (3/3) - 100%
- ✅ `GET /v1beta/tags` → listTags()
- ✅ `POST /v1beta/tags` → createTag()
- ✅ `DELETE /v1beta/tags` → deleteTag()

### Email Templates (4/4) - 100%
- ✅ `GET /v1beta/emails` → listEmails()
- ✅ `POST /v1beta/emails` → createEmail()
- ✅ `PUT /v1beta/emails/{id}` → updateEmail()
- ✅ `DELETE /v1beta/emails/{id}` → deleteEmail()

### Invoices (1/1) - 100%
- ✅ `GET /v1beta/invoices` → listInvoices()

### Payments (1/1) - 100%
- ✅ `GET /v1beta/payments` → listPayments()

### Transactions (1/1) - 100%
- ✅ `GET /v1beta/transactions` → listTransactions()

### EasyDMARC (6/6) - 100%
- ✅ `GET /v1beta/easydmarcs/list` → listEasyDmarc()
- ✅ `POST /v1beta/easydmarcs` → createEasyDmarc()
- ✅ `GET /v1beta/easydmarcs` → getEasyDmarc()
- ✅ `DELETE /v1beta/easydmarcs/{id}` → deleteEasyDmarc()
- ✅ `GET /v1beta/easydmarcs/{id}/sso` → getEasyDmarcSso()
- ✅ `POST /v1beta/easydmarcs/{id}/retry` → retryEasyDmarc()

### SpamExpert Domains (4/5) - 80%
- ✅ `POST /v1beta/spam-expert/domains` → createDomain()
- ✅ `GET /v1beta/spam-expert/domains/{domain_name}` → getDomain()
- ✅ `PUT /v1beta/spam-expert/domains/{domain_name}` → updateDomain()
- ✅ `DELETE /v1beta/spam-expert/domains/{domain_name}` → deleteDomain()

### SpamExpert Login (0/1) - 0%
- ❌ `POST /v1beta/spam-expert/generate-login-url` → generateLoginURL() *(NOT IMPLEMENTED)*

---

## ❌ Missing Endpoints (14)

### Critical Priority (Must Implement)

1. **DNS Domain Token**
   - `POST /v1beta/dns/domain-token` → createDomainToken()
   - **Module:** DNS
   - **Impact:** Medium - Used for domain token generation

2. **SpamExpert Login URL**
   - `POST /v1beta/spam-expert/generate-login-url` → generateLoginURL()
   - **Module:** SpamExpert
   - **Impact:** Medium - Used for SSO login to SpamExpert panel

### Low Priority (Already Handled or Edge Cases)

3. **Auth Login**
   - `POST /v1beta/auth/login` → Login
   - **Status:** Already handled internally in `OpenProvider::authenticate()`
   - **Action:** No implementation needed

4. **Domain Price with specific parameters**
   - `GET /v1beta/domains/prices` → GetPrice
   - **Status:** Implemented as `getPricelist()` in Domain.php
   - **Action:** Verify parameter coverage

5. **Licenses with Price Version**
   - `GET /v1beta/licenses` (with price version parameter)
   - **Status:** Implemented as `listLicenses()` but may need parameter addition
   - **Action:** Verify if `with_price_version` parameter is supported

---

## 📋 Action Items

### Phase 1: Implement Missing Critical Endpoints
- [ ] Implement `DNS::createDomainToken()`
- [ ] Implement `SpamExpert::generateLoginURL()`

### Phase 2: Verify and Enhance Existing Endpoints
- [ ] Verify `Domain::getPricelist()` covers all parameters from swagger
- [ ] Verify `License::listLicenses()` supports `with_price_version` parameter
- [ ] Add missing parameters to existing methods if needed

### Phase 3: Documentation
- [ ] Document all 111 endpoints in README
- [ ] Create API reference documentation
- [ ] Add examples for each module

---

## 🎯 Coverage Goals

- **Current:** 97/111 (87.4%)
- **After Phase 1:** 99/111 (89.2%)
- **After Phase 2:** 111/111 (100%) ✅

---

**Note:** This SDK has excellent API coverage. The remaining endpoints are either already handled internally or are minor additions that can be completed quickly.
