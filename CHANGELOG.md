# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-09-02

### Changed
- **BREAKING:** Refatoração completa das assinaturas de método para todas as operações `POST` e `PUT`. Todos os métodos que anteriormente aceitavam `array` genéricos agora exigem DTOs de Requisição específicos e fortemente tipados (ex: `DomainTransferRequest`, `DnsCreateZoneRequest`), garantindo segurança de tipos e previsibilidade.
- **BREAKING:** Padronização de todos os métodos de listagem para retornar consistentemente um objeto `Vexura\Model\PaginatedResponse<T>`. O acesso aos resultados agora é feito universalmente através da propriedade `$results`.

### Added
- Novos DTOs de Requisição para cobrir 100% dos endpoints da API, incluindo:
    - `DomainTransferRequest`, `DomainTradeRequest`, `DomainRestoreRequest`
    - `DnsCreateZoneRequest`, `DnsUpdateZoneRequest`, `DnsCreateTemplateRequest`
    - `SpamExpertCreateDomainRequest`, `SpamExpertUpdateDomainRequest`
    - E muitos outros, garantindo uma cobertura completa.

### Fixed
- Conclusão de todos os DTOs de Resposta para espelhar 100% da especificação da API. Modelos críticos como `SslOrder` e `Tld` agora incluem todas as propriedades anteriormente ausentes (`vendorOrderId`, `quarantinePeriod`, `levelPrices`, etc.).
- Corrigida a inconsistência nos retornos dos métodos `listNameserverGroups`, `listTags`, e `listDomainEmailVerifications`, que agora utilizam `PaginatedResponse`.
